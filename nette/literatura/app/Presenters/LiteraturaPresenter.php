<?php
namespace App\Presenters;
use Nette\Database\Context;
use Nette;
use Nette\Application\UI\Form;


class LiteraturaPresenter extends BasePresenter{
	private $database;

	function __construct(Context $database){
		$this->database = $database;
	}

	public function renderDefault($order = 'id'){
		$this->template->autori = $this->database
											->table('item')
											->order($order)
											->fetchAll();
	}
	
	public function renderDetail($id){
		$this->template->autor = $this->database
                                            ->table('item')
                                            ->get($id);
	}

    public function renderUpdate($id){
        $data = $this->database
                            ->table('item')
                            ->get($id);
        $data = $data->toArray();
        $this['literaturaForm']->setDefaults($data);
    }

	protected function createComponentLiteraturaForm(){
        $form = new Form;

        $form->addText('title', 'Název díla:')
                ->addRule(Form::PATTERN, 'Musí obsahovat aspoň 5 znaků', '.{5,}')
                ->setRequired(true);

        $form->addText('author', 'Autor:')
                ->addRule(Form::PATTERN, 'Musí obsahovat aspoň 5 znaků', '.{5,}')
                ->setRequired(true);

        $form->addTextArea('anotation', 'Stručná charakteristika díla:')
                ->setHtmlAttribute('rows', '6')
                ->setRequired(true);

        $form->addInteger('year', 'Rok vzniku:')
                        ->setDefaultValue(2000)
                        ->addRule(Form::MAX_LENGTH, 'Rok vzniku musí být čtyř místné číslo', 4);

        $category = [
            'drama' => 'drama',
            'poezie' => 'poezie',
            'próza' => 'próza',
        ];
        $form->addRadioList('category', 'Kategorie:', $category);

        $form->addText('stars', 'Hodnocení:')
                        ->setHtmlType('number')
                        ->setHtmlAttribute('min', '1')
                        ->setHtmlAttribute('max', '5')
                        ->setHtmlAttribute('step', '1')
                        ->setHtmlAttribute('title', 'Zadejte hodnocení v rozsahu 1 až 5')
                        ->addRule(Form::RANGE, 'Hodnocení musí být v rozsahu od 1 do 5', [1, 5]);

        $form->addSubmit('submit', 'Potvrdit');
        $form->onSuccess[] = [$this, 'LiteraturaFormSucceeded'];
        return $form;
    }   

        public function LiteraturaFormSucceeded(Form $form, \stdClass $values){
        if ($id=$this->getParameter('id')) {
            $this->database->table('item')
                    ->get($id)
                    ->update((array)$values);
            $this->flashMessage('Záznam byl aktualizován.');
        } else {
            $this->database->table('item')->insert((array)$values);
            $this->flashMessage('Byl vložen nový záznam.');            
        }            
        $this->redirect('default');
    }

    public function actionInsert(){

        $this['literaturaForm']['title'];
        $this['literaturaForm']['author'];
        $this['literaturaForm']['anotation'];
        $this['literaturaForm']['year'];
        $this['literaturaForm']['category']->setDefaultValue('próza');
        $this['literaturaForm']['stars']->setDefaultValue('3.0');
    }

	public function actionDelete($id){
        if ($this->database->table('item')->get($id)->delete($id)) {
            $this->flashMessage('Záznam byl úspěšně smazán', 'success');
        } else {
            $this->flashMessage('Došlo k nějaké chybě při mazání záznamu', 'danger');
        }
        $this->redirect('default');
    }
	
}