<?php

namespace pocketmine\command\defaults;
use pocketmine\inventory\customUI\CustomUI;
use pocketmine\inventory\customUI\elements\Button;
use pocketmine\inventory\customUI\elements\Dropdown;
use pocketmine\inventory\customUI\elements\Input;
use pocketmine\inventory\customUI\elements\Label;
use pocketmine\inventory\customUI\elements\Slider;
use pocketmine\inventory\customUI\elements\StepSlider;
use pocketmine\inventory\customUI\elements\Toggle;
use pocketmine\network\protocol\ModalFormRequestPacket;
use pocketmine\network\protocol\ModalFormResponsePacket;
use pocketmine\network\protocol\ServerSettingsRequestPacket;
use pocketmine\network\protocol\ServerSettingsResponsePacket;
use pocketmine\inventory\customUI\windows\CustomForm;
use pocketmine\inventory\customUI\windows\ModalWindow;
use pocketmine\inventory\customUI\windows\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Translate;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
class AddUICommand extends VanillaCommand{
    public function __construct($name){
        parent::__construct(
            $name,
            "%pocketmine.command.customui.description",
            "%commands.customui.usage"
        );
        $this->setPermission("pocketmine.command.customui");
    }
    public function execute(CommandSender $sender, $currentAlias, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }
        
        $this->server = Server::getInstance();
        
        $player = $this->server->getPlayer($args[0]);
        
        if(count($args) < 2){
        	$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
            return false;
        }
       
        	switch($args[1]){
        	    case "mix":
            	    $ui = new CustomForm("MixTest");
		            $ui->addElement(new Label("Label"));
		            $ui->addElement(new Dropdown("Dropdown", ["Name1", "Name2"]));
		            $ui->addElement(new Input("Input", "Text"));
		            $ui->addElement(new Slider("Slider", 5, 10, 0.5));
		            $ui->addElement(new StepSlider("StepSlider", [5, 7, 9, 11]));
		            $ui->addElement(new Toggle("Toggle"));
        	        break;
            	case "shop":
            	    $ui = new CustomForm("Shop");
		            $ui->addElement(new Label("Coming Soon!"));
        	        break;
        	    case "alert":
        	        $ui = new CustomForm("Alert");
		            $ui->addElement(new Label("You are alerted!"));
            	    break;
                case "image":
                    $ui = new SimpleForm("TestImage", "");
		            $button = new Button("ImageButton");
		            $button->setImage(Button::IMAGE_TYPE_URL, " Enter image link here");
		            $ui->addButton($button);
				    break;
				case "slider":
                    $ui = new CustomForm("TestSlider", "");
		            $ui->addElement(new Slider("Slider", 5, 10, 0.5));
				    break;
				case "input":
                    $ui = new CustomForm("TestInput", "");
		            $ui->addElement(new Input("Input", "Text"));
				    break;
				case "dropdown":
                    $ui = new CustomForm("TestDropdown", "");
		            $ui->addElement(new Dropdown("Dropdown", ["Name1", "Name2"]));
				    break;
            	    default;
                    $sender->sendMessage(TextFormat::RED . "Unknown UI Type!");
                    return true;
            	    break;
            }
        }
        
        /*$form = new SimpleForm("TestTitle");
        $player->showModal($form);
        $button = new Button("TestButton");
        $form->addButton($button);
        $slider = new Slider("TestSlider");
        $form->addSlider($slider);
        
        $ui = new CustomForm("TestWindow");
		$ui->addElement(new Label("Label"));
		$ui->addElement(new Dropdown("Dropdown", ["Name1", "Name2"]));
		$ui->addElement(new Input("Input", "Text"));
		$ui->addElement(new Slider("Slider", 5, 10, 0.5));
		$ui->addElement(new StepSlider("Stepslider", [5, 7, 9, 11]));
		$ui->addElement(new Toggle("Toggle"));*/
		
		$player->showModal($ui);
		
		/*$response = $player->checkModal($ui);
		if($response == "Button"){
			//TODO
		}*/
		
        return true;
    }
}
