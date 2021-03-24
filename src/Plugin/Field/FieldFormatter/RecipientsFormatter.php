<?php

namespace Drupal\contact_conditional_recipients\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Plugin implementation of the contact_conditional_recipients formatter.
 *
 * @FieldFormatter(
 *   id = "contact_conditional_recipients",
 *   module = "contact_conditional_recipients",
 *   label = @Translation("Recipients"),
 *   field_types = {
 *     "boolean",
 *     "list_integer",
 *     "list_string",
 *     "list_float",
 *   }
 * )
 */
class RecipientsFormatter extends FormatterBase {

  use StringTranslationTrait;

  const TOKEN = '[contact:recipients]';

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $value_recipient_map = $this->getValueRecipientMap();
    $recipients = [];
    $elements = [];

    $get_token_value = function () use ($items) {
      return contact_conditional_recipients_get_recipient_fallback($items->getParent()
        ->getValue());
    };

    foreach ($items as $item) {
      $value = $item->value;
      if (!isset($value_recipient_map[$value])) {
        continue;
      }
      $value_recipient_map[$value] = trim($value_recipient_map[$value], ', ');
      if (strstr($value_recipient_map[$value], static::TOKEN)) {
        $value_recipient_map[$value] = str_replace(static::TOKEN, $get_token_value(), $value_recipient_map[$value]);
      }
      $recipients = array_merge($recipients, explode(',', $value_recipient_map[$value]));
    }
    $recipients = array_map('trim', $recipients);
    $recipients = array_unique($recipients);
    $elements[0] = [
      '#markup' => implode(', ', $recipients),
    ];

    return $elements;
  }

  /**
   * Get the override recipient settings.
   *
   * @return array
   *   Keys are the values to match, values are the CSV recipients to use if a
   *   match is found.
   */
  private function getValueRecipientMap(): array {
    $options = explode(PHP_EOL, $this->getSetting('recipients'));
    $recipients = [];
    foreach ($options as $option) {
      list($key, $value) = explode('|', $option);
      $recipients[$key] = $value;
    }

    return $recipients;
  }

  /**
   * Defines the default settings for this plugin.
   *
   * @return array
   *   A list of default settings, keyed by the setting name.
   */
  public static function defaultSettings() {
    return ['recipients' => '*|' . static::TOKEN];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['recipients'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Recipients'),
      '#description' => $this->t('Enter one or more lines, each having this pattern <code>field value|foo@bar.com,alpha@bravo.gov.  You may insert <code>@token</code> as a placeholder for the fallback recipient(s).', ['@token' => static::TOKEN]),
      '#default_value' => $this->getSetting('recipients'),
      '#rows' => 10,
      '#required' => TRUE,
      '#resizable' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $recipients = [];
    foreach ($this->getValueRecipientMap() as $key => $value) {
      $recipients[] = $this->t('if "@value", send to %mail', [
        '@value' => $key,
        '%mail' => $value,
      ]);
    }
    $recipients = [
      '#theme' => 'item_list',
      '#items' => $recipients,
    ];
    $recipients = \Drupal::service('renderer')->render($recipients);

    $summary[] = $this->t('<strong>Recipients:</strong> @value', [
      '@value' => $recipients,
    ]);

    return $summary;
  }

}
