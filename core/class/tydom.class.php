<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__  . '/../php/tydom.inc.php';

class tydom extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */
	
	public static function pull() {
		$devices = tydomrequest('devices/data');
		$eqLogics = eqLogic::byType('tydom');

		foreach ($devices as $device) {

			$found = false;

			foreach ($eqLogics as $eqLogic) {
				if ($device->id == $eqLogic->getConfiguration('id_device','')) {
					$eqLogic_found = $eqLogic;
					$found = true;
					break;
				}
			}
			if ($found) {
				$setpoint = $device->endpoints[0]->data[1]->value;
				$temperature = $device->endpoints[0]->data[5]->value;
				$authorization = $device->endpoints[0]->data[0]->value;
				$hvacMode = $device->endpoints[0]->data[3]->value;
				$error = $device->endpoints[0]->error;
				
				$eqLogic_found->updateData($setpoint, $temperature, $authorization, $hvacMode, $error);
			}
		}
	}

    public static function syncEqLogic() {
		log::add('tydom', 'debug', "syncEqLogic()");

		$eqLogics = eqLogic::byType('tydom');

		$configs = tydomrequest('configs/file');
        foreach ($configs->endpoints as $device) {
            
            $found = false;
            
            foreach ($eqLogics as $eqLogic) {
				if ($device->id_device == $eqLogic->getConfiguration('id_device','')) {
					$eqLogic_found = $eqLogic;
					$found = true;
					break;
				}
			}
            
            if (!$found) {
				$eqLogic = new eqLogic();
				$eqLogic->setEqType_name('tydom');
				$eqLogic->setIsEnable(1);
				$eqLogic->setIsVisible(1);
                $eqLogic->setConfiguration('id_device', $device->id_device);
				$eqLogic->setName($device->id_device . '_' . $device->name);
				$eqLogic->save();

                /***********************************/
                //Infos
				$refresh = new tydomCmd();
                $refresh->setName('Rafraichir');
                $refresh->setOrder(0);
                $refresh->setEqLogic_id($eqLogic->getId());
                $refresh->setLogicalId('refresh');
                $refresh->setType('action');
                $refresh->setSubType('other');
                $refresh->save();
                if ($device->first_usage == "hvac") {
					$eqLogic->setCategory('heating', '1');
					$eqLogic->save();
					
                    $authorization = new tydomCmd();
					$authorization->setName("Mode de chauffe");
					$authorization->setEqLogic_id($eqLogic->getId());
					$authorization->setLogicalId("authorization");
                    $authorization->setOrder(1);
					$authorization->setType('info');
                    $authorization->setSubType('string');
					$authorization->save();
                    
                    $hvacMode = new tydomCmd();
					$hvacMode->setName("Mode");
					$hvacMode->setEqLogic_id($eqLogic->getId());
					$hvacMode->setLogicalId("hvacMode");
                    $hvacMode->setOrder(2);
					$hvacMode->setType('info');
                    $hvacMode->setSubType('string');
					$hvacMode->save();
                    
                    $setpoint = new tydomCmd();
					$setpoint->setName("Consigne");
					$setpoint->setEqLogic_id($eqLogic->getId());
					$setpoint->setLogicalId("setpoint");
                    $setpoint->setOrder(3);
					$setpoint->setType('info');
                    $setpoint->setSubType('numeric');
                    $setpoint->setUnite('°C');
                    $setpoint->setIsHistorized(1);
                    $setpoint->setTemplate('dashboard', 'tile');
                    $setpoint->setTemplate('mobile', 'tile');
                    $setpoint->setDisplay('generic_type', 'THERMOSTAT_SETPOINT');
                    $setpoint->setDisplay('forceReturnLineAfter', '1');
					$setpoint->setConfiguration('historizeMode', "none");
					$setpoint->setConfiguration('historyPurge', "-1 year");
					$setpoint->save();

                    $temperature = new tydomCmd();
					$temperature->setName("Température");
					$temperature->setEqLogic_id($eqLogic->getId());
					$temperature->setLogicalId("temperature");
                    $temperature->setOrder(4);
					$temperature->setType('info');
                    $temperature->setSubType('numeric');
                    $temperature->setUnite('°C');
                    $temperature->setIsHistorized(1);
                    $temperature->setTemplate('dashboard', 'tile');
                    $temperature->setTemplate('mobile', 'tile');
                    $temperature->setDisplay('generic_type', 'THERMOSTAT_TEMPERATURE');
                    $temperature->setDisplay('forceReturnLineAfter', '1');
					$temperature->setConfiguration('historizeMode', "none");
					$temperature->setConfiguration('historyPurge', "-1 year");
					$temperature->save();
                    
                    $error = new tydomCmd();
					$error->setName("Erreur");
					$error->setEqLogic_id($eqLogic->getId());
					$error->setLogicalId("error");
                    $error->setOrder(5);
					$error->setType('info');
                    $error->setSubType('numeric');
                    $error->setIsHistorized(1);
                    $error->setTemplate('dashboard', 'line');
                    $error->setTemplate('mobile', 'line');
                    $error->setDisplay('generic_type', 'GENERIC_INFO');
					$error->save();

                    $refresh_last = new tydomCmd();
                    $refresh_last->setName('Dernier refresh');
                    $refresh_last->setEqLogic_id($eqLogic->getId());
                    $refresh_last->setLogicalId('updatetime');
                    $refresh_last->setOrder(6);
                    $refresh_last->setType('info');
                    $refresh_last->setSubType('string');
                    $refresh_last->save();
				}
            }
            else {
				$eqLogic = $eqLogic_found;
                // Update !
			}
            log::add('tydom', 'debug', "id_device: " . $device->id_device);
        }
    }
    
    public static function deamon_info() {
		$return = array();
		$return['state'] = 'nok';
		$pid_file = jeedom::getTmpFolder('tydom') . '/deamon.pid';
		if (file_exists($pid_file)) {
			if (posix_getsid(trim(file_get_contents($pid_file)))) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
			}
		}
		$return['launchable'] = 'ok';
		return $return;
	}
	
	public static function deamon_start($_debug = false) {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$tydom_gateway_path = dirname(__FILE__) . '/../../resources/tydom-gateway';
        $host = config::byKey('tydom::host', 'tydom');
        $user = config::byKey('tydom::login', 'tydom');
		$password = config::byKey('tydom::password', 'tydom');
        $remote = config::byKey('tydom::remote', 'tydom');

		$cmd = 'node ' . $tydom_gateway_path . '/app.js ';
		$cmd .= $host . ' ';
		$cmd .= $user . ' ';
		$cmd .= $password . ' ';
		$cmd .= $remote . ' ';
		$cmd .= jeedom::getTmpFolder('tydom') . '/deamon.pid';
		
		log::add('tydom', 'info', 'Lancement démon tydom : ' . $cmd);
		exec($cmd . ' >> ' . log::getPathToLog('tydom') . ' 2>&1 &');
		$i = 0;
		while ($i < 30) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 30) {
			log::add('tydom', 'error', 'Impossible de lancer le démon tydom', 'unableStartDeamon');
			return false;
		}
		message::removeAll('tydom', 'unableStartDeamon');
		log::add('tydom', 'info', 'Démon tydom lancé');
	}
	
	public static function deamon_stop() {
		try {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				try {
					tydomrequest('/stop');
				} catch (Exception $e) {
					
				}
			}
			$pid_file = jeedom::getTmpFolder('tydom') . '/deamon.pid';
			if (file_exists($pid_file)) {
				$pid = intval(trim(file_get_contents($pid_file)));
				system::kill($pid);
			}
			sleep(1);
		} catch (\Exception $e) {
			
		}
	}
	
	public static function dependancy_info($_refresh = false) {
		$return = array();
		$return['log'] = 'tydom_update';
		$return['progress_file'] = jeedom::getTmpFolder('tydom') . '/dependance';
		$return['state'] = (self::compilationOk()) ? 'ok' : 'nok';
		return $return;
	}

	public static function dependancy_install() {
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('tydom') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
	}
	
	public static function compilationOk() {
		if (shell_exec('ls /usr/bin/node 2>/dev/null | wc -l') == 0) {
			return false;
		}
		return true;
	}

    /*     * *********************Méthodes d'instance************************* */
	
	public function updateData($setpoint, $temperature, $authorization, $hvacMode, $error) {
		$setpointCmd = $this->getCmd(null, 'setpoint');
        if (is_object($setpointCmd)) {
            if ($setpointCmd->formatValue($setpoint) != $setpointCmd->execCmd()) {
                $setpointCmd->setCollectDate('');
                $setpointCmd->event($setpoint);
            }
        }
            
        $temperatureCmd = $this->getCmd(null, 'temperature');
        if (is_object($temperatureCmd)) {
            if ($temperatureCmd->formatValue($temperature) != $temperatureCmd->execCmd()) {
                $temperatureCmd->setCollectDate('');
                $temperatureCmd->event($temperature);
            }
        }
            
        $authorizationCmd = $this->getCmd(null, 'authorization');
        if (is_object($authorizationCmd)) {
            if ($authorizationCmd->formatValue($authorization) != $authorizationCmd->execCmd()) {
                $authorizationCmd->setCollectDate('');
                $authorizationCmd->event($authorization);
            }
        }
            
        $hvacModeCmd = $this->getCmd(null, 'hvacMode');
        if (is_object($hvacModeCmd)) {
            if ($hvacModeCmd->formatValue($hvacMode) != $hvacModeCmd->execCmd()) {
                $hvacModeCmd->setCollectDate('');
                $hvacModeCmd->event($hvacMode);
            }
        }
		
		if ($error !== false) {
			$errorCmd = $this->getCmd(null, 'error');
			if (is_object($errorCmd)) {
				if ($errorCmd->formatValue($error) != $errorCmd->execCmd()) {
					$errorCmd->setCollectDate('');
					$errorCmd->event($error);
				}
			}
		}

        $refresh = $this->getCmd(null, 'updatetime');
        if (is_object($refresh)) {
            $refresh->event(date("d/m/Y H:i",(time())));
        }

        $mc = cache::byKey('tydomWidgetmobile' . $this->getId());
        $mc->remove();
        $mc = cache::byKey('tydomWidgetdashboard' . $this->getId());
        $mc->remove();
        $this->toHtml('mobile');
        $this->toHtml('dashboard');
        $this->refreshWidget();
	}
    
    public function refresh() {
        if ($this->getIsEnable()) {
            $device_id = $this->getConfiguration('id_device','');
            $device_data = tydomrequest('device/' . $device_id . '/endpoints');
            
            $setpoint = $device_data[1]->value;
            $temperature = $device_data[5]->value;
            $authorization = $device_data[0]->value;
            $hvacMode = $device_data[3]->value;

			$this->updateData($setpoint, $temperature, $authorization, $hvacMode, false);
        }
    }

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class tydomCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }
		log::add('tydom','debug','command: '.$this->getLogicalId());
		switch ($this->getLogicalId()) {
            case "refresh":
                $eqLogic->refresh();
                return true;
		}
        return true;
    }

    /*     * **********************Getteur Setteur*************************** */
}


