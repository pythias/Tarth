<?php
namespace Tarth\Policy;

use Tarth\Task\TaskInterface;
use Tarth\Params;

abstract class AbstractPolicy extends Params implements PolicyInterface {
    /**
     * 策略名
     * @var string
     */
    public $name = null;

    /**
     * 策略检查的优先级，值低的策略先检查
     * @var integer
     */
    protected $_order = PolicyInterface::ORDER_FOUTH;

    static public function createPolicy($policyInfo) {
        $policyName = isset($policyInfo['name']) ? $policyInfo['name'] : 'Limit';
        $className = "\\Tarth\\Policy\\" . ucfirst(strtolower($policyName)) . "Policy";
        if (class_exists($className) == false) {
            return false;
        }

        return new $className($policyInfo);
    }

    static public function sortPolicies(&$policies) {
        uasort($policies, array('self', 'sortByPriority')); 
    }

    static public function sortByPriority($policy1, $policy2) {
        if ($policy1->_order == $policy2->_order) {
            return 0;
        }

        return ($policy1->_order < $policy2->_order) ? -1 : 1;
    }

    abstract public function beforeRun(TaskInterface $task);
    abstract public function afterRun(TaskInterface $task);
}
