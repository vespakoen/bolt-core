<?php

namespace Bolt\Core\Storage;

use DateTime;

use Symfony\Component\HttpFoundation\Request;

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

        return $repository->get($wheres, false, $sort, $order, $offset, $limit, $search);
    }

    public function getForManage(ContentType $contentType, $id = null)
    {
        if (is_null($id)) {
            return $this->app['content.factory']->create(array(), $contentType);
        } else {
            $repository = $this->getReadRepository($contentType);

            return $repository->find($id);
        }
    }

    public function insert(ContentType $contentType, Request $request)
    {
        $this->fireBeforeInsertEvent($request, $contentType);

        $parameters = $request->request;

        // figure out the date fields
        $defaultFields = $contentType->getDefaultFields();
        $dateCreatedKey = $defaultFields->forPurpose('datecreated')->getKey();
        $dateUpdatedKey = $defaultFields->forPurpose('datechanged')->getKey();

        // give it a created and updated datetime
        $parameters->set($dateCreatedKey, new DateTime());
        $parameters->set($dateUpdatedKey, new DateTime());

        // give the new record an id
        $id = $this->getNewId();
        $parameters->set('id', $id);

        // get all the input
        $input = $parameters->all();

        // validate it
        if ( ! $contentType->validateInput($input)) {
            return false;
        }

        // insert it
        $repositories = $this->getWriteRepositories($contentType);
        foreach ($repositories as $repository) {
            $isSuccessful = $repository->store($input);
            if ( ! $isSuccessful) {
                return false;
            }
        }

        $this->fireAfterInsertEvent($request, $contentType, $isSuccessful);

        return true;
    }

    public function update(ContentType $contentType, Request $request, $id)
    {
        $this->fireBeforeUpdateEvent($request, $contentType, $id);

        $parameters = $request->request;
        $parameters->set('id', $id);

        // figure out the date fields
        $defaultFields = $contentType->getDefaultFields();
        $dateCreatedKey = $defaultFields->forPurpose('datecreated')->getKey();
        $dateUpdatedKey = $defaultFields->forPurpose('datechanged')->getKey();

        // unset the created datetime so it will not be updated
        $parameters->remove($dateCreatedKey);
        // update the updated datetime
        $parameters->set($dateUpdatedKey, new DateTime());

        // get all the input
        $input = $parameters->all();

        // validate it
        if ( ! $contentType->validateInput($input)) {
            return false;
        }

        // update it
        $repositories = $this->getWriteRepositories($contentType);
        foreach ($repositories as $repository) {
            $isSuccessful = $repository->update($id, $input);
            if ( ! $isSuccessful) {
                return false;
            }
        }

        $this->fireAfterUpdateEvent($request, $contentType, $id, $isSuccessful);

        return true;
    }

    public function delete(ContentType $contentType, Request $request, $id)
    {
        $this->fireBeforeDeleteEvent($request, $contentType, $id);

        $repositories = $this->getWriteRepositories($contentType);
        foreach ($repositories as $repository) {
            if ( ! $repository->delete($id)) {
                return false;
            }
        }

        $this->fireAfterDeleteEvent($request, $contentType, $id);

        return true;
    }

    public function getReadRepository($contentType)
    {
        // $this->app['repository.resolver.read']->resolve($contentType);
        return $this->app['repository.eloquent.' . $contentType->getKey()];
    }

    protected function getWriteRepositories($contentType)
    {
        // $this->app['repository.resolver.write']->resolve($contentType);
        return array(
            $this->app['repository.eloquent.' . $contentType->getKey()]
        );
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

    protected function fireBeforeInsertEvent($request, $contentType)
    {
        $event = new BeforeInsertEvent($request, $contentType);
        $this->eventDispatcher->dispatch(StorageEvents::BEFORE_INSERT, $event);
    }

    protected function fireAfterInsertEvent($request, $contentType, $isSuccessful)
    {
        $event = new AfterInsertEvent($request, $contentType, $isSuccessful);
        $this->eventDispatcher->dispatch(StorageEvents::AFTER_INSERT, $event);
    }

    protected function fireBeforeUpdateEvent($request, $contentType, $id)
    {
        $event = new BeforeUpdateEvent($request, $contentType, $id);
        $this->eventDispatcher->dispatch(StorageEvents::BEFORE_UPDATE, $event);
    }

    protected function fireAfterUpdateEvent($request, $contentType, $id, $isSuccessful)
    {
        $event = new AfterUpdateEvent($request, $contentType, $id, $isSuccessful);
        $this->eventDispatcher->dispatch(StorageEvents::AFTER_UPDATE, $event);
    }

    protected function fireBeforeDeleteEvent($request, $contentType, $id)
    {
        $event = new BeforeDeleteEvent($request, $contentType, $id);
        $this->eventDispatcher->dispatch(StorageEvents::BEFORE_DELETE, $event);
    }

    protected function fireAfterDeleteEvent($request, $contentType, $id)
    {
        $event = new AfterDeleteEvent($request, $contentType, $id);
        $this->eventDispatcher->dispatch(StorageEvents::AFTER_DELETE, $event);
    }

}
