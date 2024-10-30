<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class classJsportPredictionController
{
    private $task = null;
    private $model = null;
    public function __construct()
    {
        $this->task = filter_input(INPUT_GET,'task');
        if (!$this->task) {
            $this->task = filter_input(INPUT_GET,'view');
        }
    }

    private function getModel()
    {
        global $post_type;
        switch($post_type){
            case 'jswprediction_league':
                $this->task = 'prleaders';
                
                if(isset($_GET['action']) && $_GET['action'] == 'rounds'){
                    $this->task = 'userleague';
                }

                break;
            case 'jswprediction_round':
                $this->task = 'userround';
                break;
            
            default:

                
        }
        /*if (!$this->task) {
            $this->task = 'seasonlist';
        } else {
            if ($this->task == 'table') {
                $this->task = 'season';
            }
            if ($this->task == 'tournlist') {
                $this->task = 'tournament';
            }
        }*/
        require_once JOOMSPORT_PREDICTION_PATH_OBJECTS.'class-jsport-prediction-'.$this->task.'.php';
        $class = 'classJsport'.ucwords($this->task);
        $this->model = new $class();
    }

    public function execute()
    {
        $this->getModel();

        
        
        $rows = $this->model->getRow();

        $lists = $this->model->lists;
        $view = $this->task;
        
        if (method_exists($this->model, 'getView')) {
            $view = $this->model->getView();
        }
        $options = isset($lists['options']) ? $lists['options'] : null;
        $this->getSLHeader();
        echo '<div id="joomsport-container" class="jsIclass">
                <div class="page-content-js jmobile">';
        if(method_exists($this->model,"getHeader")){
            $this->model->getHeader();
        }
        echo jsPredictionHelper::JsHeader($options);
            //echo '<div class="under-module-header">';
            if (is_file(JOOMSPORT_PREDICTION_PATH_VIEWS.$view.'.php')) {
                require JOOMSPORT_PREDICTION_PATH_VIEWS.$view.'.php';
            }else{
                echo '<div class="error" ><p> File '.(JOOMSPORT_PREDICTION_PATH_VIEWS.$view.'.php').' doesn\'t exist</p></div>';
            }
            
            //echo '</div>';
            echo '</div>';
        if(method_exists($this->model,"getFooter")){
            $this->model->getFooter();
        }
        echo '</div>';
        $this->getSLFooter();
    }
    
    
    public function getSLHeader()
    {
        //require_once JOOMSPORT_PREDICTION_PATH_VIEWS.'elements'.DIRECTORY_SEPARATOR.'header.php';
    }
    public function getSLFooter()
    {
        //require_once JOOMSPORT_PREDICTION_PATH_VIEWS.'elements'.DIRECTORY_SEPARATOR.'footer.php';
    }
}
