<?php

namespace Bolt\Core\Storage;

use DateTime;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use Bolt\Core\Config\Object\ContentType;
use Bolt\Core\Storage\StorageEvents;
use Bolt\Core\Storage\Event\AfterDeleteEvent;
use Bolt\Core\Storage\Event\AfterInsertEvent;
use Bolt\Core\Storage\Event\AfterUpdateEvent;
use Bolt\Core\Storage\Event\BeforeDeleteEvent;
use Bolt\Core\Storage\Event\BeforeInsertEvent;
use Bolt\Core\Storage\Event\BeforeUpdateEvent;

class StorageService
{

    public function __construct($app, $eventDispatcher)
    {
        $this->app = $app;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getNew(ContentType $contentType)
    {
        $idKey = $contentType->getIdField()
            ->getKey();

        $idValue = $this->getNewId();

        return $this->app['content.factory']->create(array($idKey => $idValue), $contentType);
    }

    public function getForListing(ContentType $contentType, Request $request)
    {
        $defaultFields = $contentType->getDefaultFields();
        $defaultSort = $defaultFields->forPurpose('datechanged')->getKey();
        $sort = $request->get('sort', $defaultSort);
        $order = $request->get('order', 'desc');
        $offset = (int) $request->get('offset', 0);
        $limit = (int) $request->get('limit', 100);
        $search = $request->get('search', null);

        $repository = $this->getReadRepository($contentType);

        $wheres = $this->getWheres($contentType);

        $projectContentType = $this->app['config']->get('app/project/contenttype');
        if ($request->get('originatorContentTypeKey') == $projectContentType && $this->app['user']->hasRole('ROLE_ADMIN')) {
            $wheres = array();
        }

        return $repository->get($wheres, true, $sort, $order, $offset, $limit, $search);
    }

    public function getForManage(ContentType $contentType, $id = null)
    {
        if (is_null($id)) {
            return $this->getNew($contentType);
        } else {
            $repository = $this->getReadRepository($contentType);

            return $repository->find($id);
        }
    }

    public function insert(ContentType $contentType, ParameterBag $parameters)
    {
        $this->fireBeforeInsertEvent($parameters, $contentType);

        // give the new record an id
        $id = $this->getNewId();
        $parameters->set('id', $id);

        // validate it
        if ( ! $contentType->validateInput($parameters->all())) {
            return false;
        }

        // figure out the date fields
        $defaultFields = $contentType->getDefaultFields();
        $dateCreatedKey = $defaultFields->forPurpose('datecreated')->getKey();
        $dateUpdatedKey = $defaultFields->forPurpose('datechanged')->getKey();

        // give it a created and updated datetime
        $parameters->set($dateCreatedKey, new DateTime());
        $parameters->set($dateUpdatedKey, new DateTime());

        // insert it
        $repository = $this->getWriteRepository($contentType);
        $isSuccessful = $repository->store($parameters->all());
        if ( ! $isSuccessful) {
            return false;
        }

        $relationData = $parameters->get('links', array());

        $this->updateRelations($contentType, $id, $relationData);

        $this->fireAfterInsertEvent($parameters, $contentType, $isSuccessful);

        return true;
    }

    public function update(ContentType $contentType, ParameterBag $parameters, $id)
    {
        $this->fireBeforeUpdateEvent($parameters, $contentType, $id);

        $parameters->set('id', $id);

        // figure out the date fields
        $defaultFields = $contentType->getDefaultFields();
        $dateCreatedKey = $defaultFields->forPurpose('datecreated')->getKey();
        $dateUpdatedKey = $defaultFields->forPurpose('datechanged')->getKey();

        // unset the created datetime so it will not be updated
        $parameters->remove($dateCreatedKey);

        // validate it
        if ( ! $contentType->validateInput($parameters->all())) {
            return false;
        }

        // update the updated datetime
        $parameters->set($dateUpdatedKey, new DateTime());

        // update it
        $repository = $this->getWriteRepository($contentType);
        $isSuccessful = $repository->update($id, $parameters->all());
        if ( ! $isSuccessful) {
            return false;
        }

        $relationData = $parameters->get('links', array());

        $this->updateRelations($contentType, $id, $relationData);

        $this->fireAfterUpdateEvent($parameters, $contentType, $id, $isSuccessful);

        return true;
    }

    public function delete(ContentType $contentType, ParameterBag $parameters, $id)
    {
        $this->fireBeforeDeleteEvent($parameters, $contentType, $id);

        $repository = $this->getWriteRepository($contentType);
        if ( ! $repository->delete($id)) {
            return false;
        }

        $this->fireAfterDeleteEvent($parameters, $contentType, $id);

        // remove all relations from and to this content
        $this->updateRelations($contentType, $id, array());

        return true;
    }

    protected function updateRelations($contentType, $firstId, $relationData)
    {
        $repository = $this->getWriteRepository($contentType);
        $repository->updateRelations($firstId, $relationData);
    }

    public function getReadRepository($contentType)
    {
        // $this->app['repository.resolver.read']->resolve($contentType);
        return $this->app['repository.eloquent.' . $contentType->getKey()];
    }

    protected function getWriteRepository($contentType)
    {
        // $this->app['repository.resolver.write']->resolve($contentType);
        return $this->app['repository.eloquent.' . $contentType->getKey()];
    }

    protected function getNewId()
    {
        $connection = $this->app['db'];
        $sql = 'SELECT ' . $connection->getDatabasePlatform()->getGuidExpression();
        return $connection->query($sql)->fetchColumn(0);
    }

    protected function getWheres($contentType)
    {
        // filter if filter=true, only filter when filter=false if the user is an admin
        if ($contentType->get('filter', true) == false && $this->app['user']->hasRole('ROLE_ADMIN')) {
            return array();
        }

        // if we are filtering projects, it cannot be done by the relationship but must be filtered on the id
        $contentTypeIsCurrentProject = $this->app['project.service']->isProjectsContentType($contentType);
        if ($contentTypeIsCurrentProject) {
            return array(
                $contentType->getTableName() . '.id' => $this->app['project.service']->getCurrentProjectId()
            );
        }

        // filter on the relation to a project
        return array(
            'incoming.to_id' => $this->app['project.service']->getCurrentProjectId()
        );
    }

    protected function fireBeforeInsertEvent($parameters, $contentType)
    {
        $event = new BeforeInsertEvent($parameters, $contentType);
        $this->eventDispatcher->dispatch(StorageEvents::BEFORE_INSERT, $event);
    }

    protected function fireAfterInsertEvent($parameters, $contentType, $isSuccessful)
    {
        $event = new AfterInsertEvent($parameters, $contentType, $isSuccessful);
        $this->eventDispatcher->dispatch(StorageEvents::AFTER_INSERT, $event);
    }

    protected function fireBeforeUpdateEvent($parameters, $contentType, $id)
    {
        $event = new BeforeUpdateEvent($parameters, $contentType, $id);
        $this->eventDispatcher->dispatch(StorageEvents::BEFORE_UPDATE, $event);
    }

    protected function fireAfterUpdateEvent($parameters, $contentType, $id, $isSuccessful)
    {
        $event = new AfterUpdateEvent($parameters, $contentType, $id, $isSuccessful);
        $this->eventDispatcher->dispatch(StorageEvents::AFTER_UPDATE, $event);
    }

    protected function fireBeforeDeleteEvent($parameters, $contentType, $id)
    {
        $event = new BeforeDeleteEvent($parameters, $contentType, $id);
        $this->eventDispatcher->dispatch(StorageEvents::BEFORE_DELETE, $event);
    }

    protected function fireAfterDeleteEvent($parameters, $contentType, $id)
    {
        $event = new AfterDeleteEvent($parameters, $contentType, $id);
        $this->eventDispatcher->dispatch(StorageEvents::AFTER_DELETE, $event);
    }

}
