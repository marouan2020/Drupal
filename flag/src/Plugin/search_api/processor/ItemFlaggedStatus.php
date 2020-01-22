<?php
/**
 * @author Ben Mansour Marouan
 * @website https://www.linkedin.com/in/magento-tow-developer/
 * @mail marouan.ben.mansour@gmail.com
 * @date  22/01/2020
 */
namespace Drupal\flag\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\flag\Plugin\search_api\processor\Property\ItemFlaggedStatusProperty;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds an additional field containing the flagged status of the indexed items.
 *
 * @SearchApiProcessor(
 *   id = "item_flagged_status",
 *   label = @Translation("Flagged status"),
 *   description = @Translation("Adds an additional field containing the flagged status of the indexed items."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = false,
 *   hidden = false,
 * )
 */
class ItemFlaggedStatus extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];
    if (!$datasource) {
      $definition                        = ['label' => $this->t('Flagged status'), 'description' => $this->t('Adds an additional field containing the flagged status of the indexed items'), 'type' => 'boolean', 'processor_id' => $this->getPluginId(),];
      $properties['item_flagged_status'] = new ItemFlaggedStatusProperty($definition);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $this->getEntity($item->getOriginalObject());
    $fields = $this->getFieldsHelper()->filterForPropertyPath($item->getFields(), NULL, 'item_flagged_status');
    foreach ($fields as $field) {
      $flag_id      = $field->getConfiguration()['flag'];
      $flag_service = \Drupal::service('flag');
      $flag         = $flag_service->getFlagById($flag_id);
      if (!empty($flag)) {
        $field->addValue($flag->isFlagged($entity));
      }
    }
  }

  /**
   * Retrieves the node related to an indexed search object.
   *
   * Will be either the node itself, or the node the comment is attached to.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $item
   *   A search object that is being indexed.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity related to that search object.
   */
  protected function getEntity(ComplexDataInterface $item) {
    $entity = $item->getValue();
    if ($entity instanceof EntityInterface) {
      return $entity;
    }
    return NULL;
  }
}
