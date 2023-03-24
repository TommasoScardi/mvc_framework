<?php

namespace MvcFramework\Core;

/**
 * The Service interface is necessary to create a new custom service
 * @package MvcFramework\Core
 */
interface Service
{
    /**
     * Function called before the router inject all the serivce dependency into controllers
     * @return bool true on success, false otherwise
     * @throws ServiceExceptio when some fatal errors appens
     */
    public function init();
}
