<?php

namespace Bolt\Core\Controller;

final class ControllerEvents
{
    const BEFORE_INSERT = 'controller.before_insert';

    const AFTER_INSERT = 'controller.after_insert';

    const BEFORE_UPDATE = 'controller.before_update';

    const AFTER_UPDATE = 'controller.after_update';

    const BEFORE_DELETE = 'controller.before_delete';

    const AFTER_DELETE = 'controller.after_delete';

    const BEFORE_REORDER = 'controller.before_reorder';

    const AFTER_REORDER = 'controller.after_reorder';
}
