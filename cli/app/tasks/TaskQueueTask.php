<?php

namespace app\tasks;

use Exception;
use RedisException;

/**
 * TaskQueueTask 任务队列服务
 */
class TaskQueueTask extends MainTask
{

    public function mainAction()
    {
        while (TRUE) {
        	try {
                $sPushMqKey = 'task:mq';
        		$result = $this->redis->brpop([$sPushMqKey], 30);

        		if ($result && $result[0] == $sPushMqKey) {
        			print_r($result);
        			$aTask = unserialize($result[1]);
        			print_r($aTask);

	        		// 一天之前的不处理
		        	if ($aTask['time'] > (time()-60*60*24)) {
						$sTask   = 'app\\tasks\\'. ucfirst($aTask['data']['task'].'Task');
						$sAction = $aTask['data']['action'].'Action';
						$aParam  = isset($aTask['data']['param']) ? $aTask['data']['param'] : NULL;

		        		if (class_exists($sTask)) {
		        			$oTask = new $sTask($aTask['data']['param']);
		        			if(method_exists($oTask, $sAction)) {
                                echo sprintf("[%s] mq %s task %s action pop.\n", date('r'), $sTask, $sAction);
		        				$flg = $oTask->$sAction($aParam);
		        				var_dump($flg);
		        			}
		        		}
		        	}
        		}
        		
        	} catch (RedisException $e) {
        		echo $e;
                try {
                    $this->redis->connect();

                    if ($this->config->redis->pconnect) {
                        $this->redis->pconnect($this->config->redis->host, $this->config->redis->port);
                    } else {
                        $this->redis->connect($this->config->redis->host, $this->config->redis->port);
                    }

                    $this->redis->auth($this->config->redis->auth);
                    $this->redis->select($this->config->redis->db);
                } catch (Exception $e) {
                    echo $e;
                }

			} catch (\Phalcon\Db\Exception $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            } catch (\PDOException $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            } catch (Exception $e) {
        		echo $e;
        	}

            echo sprintf("[%s] queue task empty.\n", date('r'));
            sleep(1);
        }
    }
}