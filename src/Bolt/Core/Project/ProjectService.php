<?php

namespace Bolt\Core\Project;

use Bolt\Core\Config\Object\Content;
use Bolt\Core\Config\Object\ContentType;

class ProjectService
{

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function setupCurrentProject()
    {
        $app = $this->app;
        $app['project'] = $app['projects']->get($app['session']->get('project_id'));
        if ( ! $app['project']) {
            $project = $app['projects']->first();
            $this->setCurrentProject($project);
        }
    }

    /**
     * Set the current project
     *
     * @param Bolt\Core\Config\Object\Content $project The new project
     */
    public function setCurrentProject(Content $project)
    {
        $this->app['project'] = $project;
        $this->app['session']->set('project_id', $project->getId());
        $this->app['session']->set('project_namespace', str_replace('.', '', $project->get('namespace')));
    }

    /**
     * Get's the current project
     *
     * @return Bolt\Core\Config\Object\Content
     */
    public function getCurrentProject()
    {
        return $this->app['project'];
    }

    public function isProjectsContentType(ContentType $contentType)
    {
        return $contentType->getKey() == $this->getProjectKey();
    }

    public function getProjectKey()
    {
        return $this->app['config']->get('app/project/contenttype');
    }

    /**
     * Get's the id of the current project
     *
     * @return string The id
     */
    public function getCurrentProjectId()
    {
        return $this->getCurrentProject()->getId();
    }

}
