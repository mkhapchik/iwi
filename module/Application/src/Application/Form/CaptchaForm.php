<?php
namespace Application\Form;
 
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Session\Container;

abstract class CaptchaForm extends Form
{
    /**
    *  Инициализация элементов формы
    */
	abstract protected function initForm();
	
	protected $captchaCounter;
	
	public function __construct($name = null)
    {
        parent::__construct($name);
		
		$this->initCaptchaCounter();
		
		$this->initForm();
		//if($this->getCounter() > 3) $this->addCaptcha();
				
		$this->addFormNameField();
				
		$this->addButtons();
		
		$inputFilter = $this->__getInputFilter();
		$this->setInputFilter($inputFilter);
		
    }

	public function addFormNameField()
	{
		$this->add(array(
            'name' => 'form_name',
            'attributes' => array(
                'type'  => 'hidden',
				'value' => $this->getName(),
            ),
            'options' => array(
                'label' => '',
            ),
        ));	
	}
	
	public function getCounter()	
	{
		return $this->captchaCounter;
	}
	
	protected function initCaptchaCounter()
	{
		$formCont = new Container($this->getFormNamespace());
		if(isset($formCont->counter)) $counter = $formCont->counter;
		else $counter = 0;
		
		$this->captchaCounter = $counter;
	}
	
	public function incrementCounter()
	{
		$formCont = new Container($this->getFormNamespace());
		if(isset($formCont->counter)) $formCont->counter++;
		else $formCont->counter = 1;
		
		$this->captchaCounter++;
	}
	
	public function resetCounter()
	{
		$formCont = new Container($this->getFormNamespace());
		$formCont->counter=0;
	}
	
	protected function getFormNamespace()
	{
		return get_class($this);
	}
	
	public function addCaptcha()
	{
		if(!$this->has('captcha'))
		{
			$this->add(array(
				'type' => 'Captcha',
				'name' => 'captcha',
				'attributes'=> array(
					'class'=>'required'
				),
				'options' => array(
					'label' => 'Введите код с картинки',
					'priority'=>1,
					'useAsBaseFieldset'=>true,
					'captcha' => array(
						//'class' => 'Dumb',
						'class' => 'Image',
						'font'=>'./data/captcha/font/arial.ttf',
						'imgUrl'=>'/img/captcha/',
						'imgDir'=>'public/img/captcha/',
						'gcFreq'=>'1', // частота вызова сборщика мусора
						'expiration'=>'60', //время актуальности файлов изображений капчи
						'timeout'=>'60', // время жизни сессии
						'wordlen'=>'4',
						'keepSession'=>false, //создать одну сессию ?????????
						'width'=>120,
						'height' => 50,
						'fontSize'=>20,
						'dotNoiseLevel' => 50,
						'lineNoiseLevel' => 2,
						//'class'=>'Figlet'
						'messages' => array(
							\Zend\Captcha\AbstractWord::BAD_CAPTCHA => "Неверно указан код",
							\Zend\Captcha\AbstractWord::MISSING_VALUE => "Ошибка верификации кода",
							\Zend\Captcha\AbstractWord::MISSING_ID => "Ошибка верификации кода",
						),
						
					),
					
				)
			));
		}
	}
	
	protected function addButtons()
	{
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Войти',
            ),
        ),array(
			'priority'=>-1
		));
	}
	
	protected function __getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();

		return $inputFilter;
	}
	
	
	
}