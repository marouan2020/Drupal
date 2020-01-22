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
use Drupal\flag\Plugin\search_api\processor\Property\ItemFlaggedUsersProperty;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\flag\FlagInterface;


/**
 * Adds an additional field containing the flagged users of the indexed items.
 *
 * @SearchApiProcessor(
 *   id = "item_flagged_users",
 *   label = @Translation("Flagged users"),
 *   description = @Translation("Adds an additional field containing the flagged users of the indexed items."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = false,
 *   hidden = false,
 * )
 */
class ItemFlaggedUsers extends ProcessorPluginBase {

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
      $definition                       = ['label' => $this->t('Flagged users'), 'description' => $this->t('Adds an additional field containing the flagged usres of the indexed items'), 'type' => 'integer', 'processor_id' => $this->getPluginId(),];
      $properties['item_flagged_users'] = new ItemFlaggedUsersProperty($definition);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $this->getEntity($item->getOriginalObject());
    $fields = $this->getFieldsHelper()->filterForPropertyPath($item->getFields(), NULL, 'item_flagged_users');
    foreach ($fields as $field) {
      $flag_id      = $field->getConfiguration()['flag_users'];
      $flag_service = \Drupal::service('flag');
      $flag         = $flag_service->getFlagById($flag_id);
      $idsUsers     = $this->getFlaggingUsers($entity, $flag);
      $field->setValues($idsUsers);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getFlaggingUsers(EntityInterface $entity, FlagInterface $flag = NULL) {
    $flaggingManager = \Drupal::entityTypeManager()->getStorage('flagging');
    $query           = $flaggingManager->getQuery();
    $query->condition('entity_type', $entity->getEntityTypeId())->condition('entity_id', $entity->id());
    if (!empty($flag)) {
      $query->condition('flag_id', $flag->id());
    }
    $ids     = $query->execute();
    $userIds = [];
    foreach ($flaggingManager->loadMultiple($ids) as $flagging) {
      $userIds[] = $flagging->get('uid')->first()->getValue()['target_id'];
    }
    return $userIds;
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
