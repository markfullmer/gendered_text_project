<?php

namespace Drupal\gendered_text_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gendered_text_api\GenderedText;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Routing\RouteSubscriber;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\gendered_text_api\Controller\ReplacementMapController;

/**
 * Class TestForm.
 */
class TestForm extends FormBase {
  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;  

  /**
   * Creates a Prepare form.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, RequestStack $request_stack) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('request_stack')          
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'selector_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Workaround for using the tempStore. 
    // See https://www.drupal.org/project/drupal/issues/2743931
    $this->requestStack->getCurrentRequest()->getSession()->set('forced', TRUE);
    $text = $this->tempStoreFactory->get('testForm')->get($this->currentUser()->id());
    $this->tempStoreFactory
        ->get('testForm')
        ->set($this
        ->currentUser()
        ->id(), '');
    if (!empty($text)) {
        $default = $text;
    }
    else {
        $default = 'One midday when, after an absence of two hours, {{ Arabella }} came into the room, {{ she(Arabella) }} beheld the chair empty. Down {{ she(Arabella) }} flopped on the bed, and sitting, meditated.' . PHP_EOL . PHP_EOL . '"Now where the devil is my {{ man(Jude) }} gone to!" {{ she(Arabella) }} said.';
    }
    $legend = $this->tempStoreFactory->get('testLegend')->get($this->currentUser()->id());
    $this->tempStoreFactory
    ->get('testLegend')
    ->set($this
    ->currentUser()
    ->id(), '');
    if (!$legend) {
        $default_legend = '[ [Arabella/Arthur/Aspen:male] [Julie/Jude/Juen:female] ]';
    }
    else {
        $default_legend = $legend;
    }
    $form = [];
    $form['#cache'] = [
      'max-age' => 0
    ];
    $form['input'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Text with placeholders'),
        '#default_value' => $default,
    ];
    $form['legend'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Character Legend'),
        '#default_value' => $default_legend,
    ];    

    $form['test-submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test genders'),
      '#attributes' => ['class' => ['btn', 'btn-primary']],
    ];
    if (!empty($legend) && !empty($text)) {
        $form['output'] = [
            '#type' => 'details',
            '#title' => t('Genderized Text'),
            '#open' => TRUE,
        ];
        $replacements = ReplacementMapController::getReplacementMap();
        $output = GenderedText::process($text . $legend, $replacements);
        $form['output']['text']['#markup'] = nl2br($output);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $text = $form_state->getValue('input');
    $this->tempStoreFactory
    ->get('testForm')
    ->set($this
    ->currentUser()
    ->id(), $text);

    $legend = $form_state->getValue('legend');
    $this->tempStoreFactory
    ->get('testLegend')
    ->set($this
    ->currentUser()
    ->id(), $legend);
    return;
  }
}
