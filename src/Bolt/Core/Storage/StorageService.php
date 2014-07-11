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
