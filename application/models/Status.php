<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cem
 * Date: 26.02.13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */

class Application_Model_Status extends CD_Model {
    protected $_tableName = 'states';

    protected $_fieldLabels = array(
        'name'  => 'Name'
    );
}