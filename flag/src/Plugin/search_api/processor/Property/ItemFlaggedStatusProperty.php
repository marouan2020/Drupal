<?php
/**
 * @author Ben Mansour Marouan
 * @website https://www.linkedin.com/in/magento-tow-developer/
 * @mail marouan.ben.mansour@gmail.com
 * @date  22/01/2020
 */
namespace Drupal\flag\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ConfigurablePropertyBase;

/**
 * Class ItemFlaggedStatusProperty
 *
 * Defines a "item_flagged_status" property.
 *
 * @package Drupal\flag\Plugin\search_api\processor\Property
 */
class ItemFlaggedStatusProperty extends ConfigurablePropertyBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['flag' => []];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $configuration    = $field->getConfiguration();
    $form['#tree']    = TRUE;
    $flag_bundle_info = \Drupal::service('entity_type.bundle.info')->getBundleInfo('flagging');
    $flags            = array_map(function ($info){
      return $info['label'];
    }, $flag_bundle_info);
    $form['flag']     = [
      '#type' => 'select',
      '#title' => $this->t('Flag'),
      '#required' => TRUE,
      '#description' => $this->t('The flag to index the status for (only global flags are supported for now).'),
      '#default_value' => $configuration['flag'],
      '#options' => $flags
    ];
    return $form;
  }

}
