<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramsadmin
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * BramsAdmin Component Controller
 *
 * @since  0.0.1
 */
class BramsAdminController extends BaseController {
    /**
     * CHANGES : if $block_display is set to true, the function
     *  will NOT call the views display method and returns the view instead.
     *
     * Typical view method for MVC based architecture
     *
     * This function is provide as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param boolean $cacheable If true, the view output will be cached
     * @param array $url_params An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return BramsAdminController|JViewLegacy
     *
     * @throws Exception
     * @since   3.0
     */
    public function display($cacheable = false, $url_params = array(), $block_display = false)
    {
        $document = Factory::getDocument();
        $viewType = $document->getType();
        $viewName = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

        // Get/Create the model
        if ($model = $this->getModel($viewName)) {
            // Push the model into the view (as default)
            $view->setModel($model, true);
        } elseif ($model = $this->getModel(substr($viewName, 0, -4) . 's')) {
             // Push the model into the view (as default)
             $view->setModel($model, true);
        }

        $view->document = $document;

        // Display the view
        if ($cacheable && $viewType !== 'feed' && JFactory::getConfig()->get('caching') >= 1) {
            $option = $this->input->get('option');

            if (is_array($url_params)) {
                $app = JFactory::getApplication();

                if (!empty($app->registeredurlparams)) {
                    $registeredurlparams = $app->registeredurlparams;
                } else {
                    $registeredurlparams = new \stdClass;
                }

                foreach ($url_params as $key => $value) {
                    // Add your safe URL parameters with variable type as value {@see \JFilterInput::clean()}.
                    $registeredurlparams->$key = $value;
                }

                $app->registeredurlparams = $registeredurlparams;
            }

            /** @var JCacheControllerView $cache */
            $cache = Factory::getCache($option, 'view');
            $cache->get($view);

        } elseif($block_display) {
            return $view;
        } else {
            $view->display();
        }

        return $this;
    }

    /**
     * API - POST
     * Function executes the views new method. This function is executed when a new
     * database row has to be created.
     *
     * @since 0.7.3
     */
    public function new() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->new();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - DELETE
     * Function executes the view delete method. This function is called when
     * a database row has to be deleted.
     * The row depends on the view that will be called
     *
     * @since 0.7.3
     */
    public function delete() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->delete();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the views getOne method. This method
     * will get all the information about equivalent elements and return this
     * to the sites front-end.
     * The element info that will be returned depends on the view.
     *
     * @since 0.7.3
     */
    public function getAll() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getAll();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - PUT
     * Function executes the views update method. This function is called when
     * a database row has to be updated.
     * The database row to be updated depends on the specified view.
     *
     * @since 0.7.3
     */
    public function update() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->update();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getOne method. THis function is called when
     * front-end needs information about one specific row.
     * The database row to return depends on the specified view.
     *
     * @since 0.7.3
     */
    public function getOne() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getOne();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getLocationAntennas() method. This function is called when
     * front-end needs all the available locations.
     *
     * @since 0.3.0
     */
    public function getLocationAntennas() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getLocationAntennas();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the view getSystemNames() method. This function is called when
     * front-end needs all the taken system names.
     *
     * @since 0.3.0
     */
    public function getSystemNames() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getSystemNames();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /** + LOCATION EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the locationEdit view getLocationCodes method.
     * This function is called when the front-end of the site needs all
     * the locations with the location code.
     *
     * @since 0.4.2
     */
    public function getLocationCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getLocationCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /**
     * API - GET
     * Function executes the locationEdit view getCountries method.
     * This function is called when the front-end of the site needs all
     * the countries from the database.
     *
     * @since 0.4.2
     */
    public function getCountries() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getCountries();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    // * GET api for observers is to be found in the OBSERVERS view part

    /** + OBSERVERS VIEW APIs */
    /**
     * API - GET
     * Function executes the specified view getObservers method.
     * This function is called when the front-end of the site needs all
     * the observers from the database.
     *
     * @since 0.4.2
     */
    public function getObservers() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getObservers();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    /** + OBSERVER EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the observerEdit view getObserverCodes method.
     * This function is called when the front-end of the site needs all
     * the observer codes.
     *
     * @since 0.5.2
     */
    public function getObserverCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getObserverCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    // * getCountries goes trough the same task as in the LOCATION EDIT part

    /** + BEACON EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the given view getBeaconCodes method.
     * This function is called when the front-end of the site needs all
     * the beacon codes.
     *
     * @since 0.6.2
     */
    public function getBeaconCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getBeaconCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }

    // * getCountries goes trough the same task as in the LOCATION EDIT part

    /** + ANTENNA EDIT VIEW APIs */
    /**
     * API - GET
     * Function executes the given view getAntennaCodes method.
     * This function is called when the front-end of the site needs all
     * the antenna codes.
     *
     * @since 0.7.2
     */
    public function getAntennaCodes() {
        if (Jsession::checkToken('get')) {
            try {
                $view = $this->display(false, array(), true);
            } catch (Exception $e) {
                echo new JResponseJson(array(('message') => $e));
                Log::add($e, Log::ERROR, 'error');
                return;
            }
            $view->getAntennaCodes();
        } else {
            echo new JResponseJson(array(('message') => false));
        }
    }
}
