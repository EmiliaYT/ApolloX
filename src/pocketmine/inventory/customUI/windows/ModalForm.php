<?php

namespace pocketmine\inventory\customUI\windows;

use pocketmine\Player;
use pocketmine\inventory\customUI\CustomUI;

class ModalForm implements CustomUI, \JsonSerializable{

	/** @var string */
	protected $title = '';
	/** @var string */
	protected $content = '';
	/** @var string */
	protected $trueButtonText = '';
	/** @var string */
	protected $falseButtonText = '';

	/**
	 * This is a window to show a simple text to the player
	 *
	 * @param string $title
	 * @param string $content
	 * @param string $trueButtonText
	 * @param string $falseButtonText
	 */
	public function __construct($title, $content, $trueButtonText, $falseButtonText){
		$this->title = $title;
		$this->content = $content;
		$this->trueButtonText = $trueButtonText;
		$this->falseButtonText = $falseButtonText;
	}

	final public function jsonSerialize(){
		return [
			'type' => 'modal',
			'title' => $this->title,
			'content' => $this->content,
			'button1' => $this->trueButtonText,
			'button2' => $this->falseButtonText,
		];
	}

	/**
	 * To handle manual closing
	 * @param Player $player
	 */
	public function close(Player $player){
		
	}

	/**
	 * @param array $response
	 * @param Player $player
	 * @return boolean depending on which button was clicked
	 */
	final public function handle($response, Player $player){
		return $response[0];
	}

	final public function getTitle(){
		return $this->title;
	}

	public function getContent(){
		return [$this->content, $this->trueButtonText, $this->falseButtonText];
	}
	
}
