<?php

namespace Bolt\Core\Storage;

final class StorageEvents
{
    const BEFORE_INSERT = 'storage.before_insert';

    const AFTER_INSERT = 'storage.after_insert';

    const BEFORE_UPDATE = 'storage.before_update';

    const AFTER_UPDATE = 'storage.after_update';

    const BEFORE_DELETE = 'storage.before_delete';

    const AFTER_DELETE = 'storage.after_delete';

    const BEFORE_REORDER = 'storage.before_reorder';

    const AFTER_REORDER = 'storage.after_reorder';

    const RELATIONS_ADDED = 'storage.relations_added';

    const RELATIONS_DELETED = 'storage.relations_deleted';
}
