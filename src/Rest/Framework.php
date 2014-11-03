<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 01.11.14
 * Time: 11:29
 */

namespace Rest;

use Client\WindowsAzureErrorCodes;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use WindowsAzure\Table\Internal\ITable;
use WindowsAzure\Table\Models\EdmType;
use WindowsAzure\Table\Models\Entity;
use WindowsAzure\Table\Models\GetEntityResult;


class Framework
{

    private $tableProxy;

    /**
     * Returns json-encoded list of frameworks with number of likes
     * @param Request $request
     * @param Application $app
     * @return string
     */
    public function get(Request $request, Application $app)
    {
        $frameworks = $app['azure.table']
                ->queryEntities("frameworks")
                ->getEntities();
        $response = [];
        /**
         * @var Entity $framework
         */
        foreach ($frameworks as $framework) {
            $response[] = [
                'key' => $framework->getRowKey(),
                'name' => $framework->getPropertyValue('name'),
                'votes' => $framework->getPropertyValue('votes')
            ];
        }
        return json_encode($response);
    }

    /**
     * Create new framework
     * @param Request $request
     * @param Application $app
     */
    public function post(Request $request, Application $app)
    {
        $name = $request->get('name');
        $key = md5($request->get('name'));

        $framework = new Entity();
        $framework->setPartitionKey('framework');
        $framework->setRowKey($key);
        $framework->addProperty('name', EdmType::STRING, $name);
        try {
            $app['azure.table']->insertEntity('frameworks', $framework);
            return json_encode(['key' => $key]);
        } catch (\Exception $e) {
            return json_encode(['type'=> get_class($e), 'message' => $e->getMessage()]);
        }
    }

    /**
     * Vote for framework
     * @param Request $request
     * @param Application $app
     */
    public function put(Request $request, Application $app)
    {
        /**
         * @var ITable $table
         */
        $table = $app['azure.table'];
        try {
            /**
             * @var Entity $framework
             */
            $framework = $table
                ->getEntity('frameworks', 'framework', $request->get('key'))
                ->getEntity();
            if (null === $framework->getPropertyValue('votes')) {
                $framework->addProperty('votes', EdmType::INT32, 1);
            } else {
                $framework->setPropertyValue('votes', $framework->getPropertyValue('votes') + 1);
            }
            $table->mergeEntity('frameworks', $framework);
            return json_encode(['key' => $framework->getRowKey(), 'votes' => $framework->getPropertyValue('votes')]);
        } catch (\Exception $e) {
            return json_encode(['type' => get_class($e), 'message' => $e->getMessage()]);
        }
    }

} 