<?php

/**
 * @file
 * Create field storage and field instances for Nanomer Edition 4 module.
 */

namespace Drupal\pre_nanomer_edition;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

class InstallFields {

  /**
   * Create all fields for the Nanomer Edition 4 content type.
   */
  public static function createFields() {
    // Definición de campos simples que se iteran automáticamente en la plantilla
    $secciones = [
      'hero_titulo' => ['label' => 'Hero - Título Principal', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'hero_descripcion' => ['label' => 'Hero - Subtítulo', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'objetivo_contenido' => ['label' => 'Objetivo - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Fechas Importantes - campos simples (sin HTML)
      'fechas_titulo' => ['label' => 'Fechas - Título/Fecha', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'fechas_descripcion' => ['label' => 'Fechas - Descripción', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'nota_fechas' => ['label' => 'Nota de Fechas', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      'requisitos_contenido' => ['label' => 'Requisitos - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Documentos - campos simples
      'documentos_titulo' => ['label' => 'Documentos - Título', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'documentos_descripcion' => ['label' => 'Documentos - Descripción', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      
      'procedimiento_contenido' => ['label' => 'Procedimiento - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      'criterios_contenido' => ['label' => 'Criterios - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      'becas_contenido' => ['label' => 'Becas - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Compromisos - campo simple repetible
      'compromisos_descripcion' => ['label' => 'Compromisos - Descripción', 'type' => 'text', 'cardinality' => -1, 'required' => FALSE],
      
      'contacto_email' => ['label' => 'Contacto - Email', 'type' => 'email', 'cardinality' => 1, 'required' => FALSE],
      'contacto_contenido' => ['label' => 'Contacto - Información Adicional', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
    ];

    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        \Drupal::logger('pre_nanomer_edition')->info('Creating field @name of type @type', [
          '@name' => $field_name_full,
          '@type' => $field_config['type'],
        ]);

        // Crear field storage
        $field_storage = FieldStorageConfig::loadByName('node', $field_name_full);
        if (!$field_storage) {
          $field_storage = FieldStorageConfig::create([
            'field_name' => $field_name_full,
            'entity_type' => 'node',
            'type' => $field_config['type'],
            'cardinality' => $field_config['cardinality'],
          ]);
          $field_storage->save();
          \Drupal::logger('pre_nanomer_edition')->info('Field storage created: @name', ['@name' => $field_name_full]);
        } else {
          \Drupal::logger('pre_nanomer_edition')->info('Field storage already exists: @name', ['@name' => $field_name_full]);
        }

        // Crear field instance
        $field = FieldConfig::loadByName('node', 'pre_nanomer_edition', $field_name_full);
        if (!$field) {
          $field = FieldConfig::create([
            'field_storage' => $field_storage,
            'bundle' => 'pre_nanomer_edition',
            'label' => $field_config['label'],
            'required' => $field_config['required'],
            'translatable' => TRUE,
          ]);
          
          // Deshabilitar text processing para campos text_long
          if ($field_config['type'] === 'text_long') {
            $field->setThirdPartySetting('field_ui', 'default_widget', 'text_textarea');
          }
          
          $field->save();
          \Drupal::logger('pre_nanomer_edition')->info('Field config created: @name', ['@name' => $field_name_full]);
        } else {
          \Drupal::logger('pre_nanomer_edition')->info('Field config already exists: @name', ['@name' => $field_name_full]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('pre_nanomer_edition')->error('Error creating field @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    // Configurar display (form y view)
    $entity_form_display = EntityFormDisplay::load('node.pre_nanomer_edition.default');
    if (!$entity_form_display) {
      $entity_form_display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'pre_nanomer_edition',
        'mode' => 'default',
        'status' => TRUE,
      ]);
      \Drupal::logger('pre_nanomer_edition')->info('Created new EntityFormDisplay for pre_nanomer_edition');
    } else {
      \Drupal::logger('pre_nanomer_edition')->info('Loaded existing EntityFormDisplay for pre_nanomer_edition');
    }

    $entity_view_display = EntityViewDisplay::load('node.pre_nanomer_edition.default');
    if (!$entity_view_display) {
      $entity_view_display = EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'pre_nanomer_edition',
        'mode' => 'default',
        'status' => TRUE,
      ]);
      \Drupal::logger('pre_nanomer_edition')->info('Created new EntityViewDisplay for pre_nanomer_edition');
    } else {
      \Drupal::logger('pre_nanomer_edition')->info('Loaded existing EntityViewDisplay for pre_nanomer_edition');
    }

    // Configurar los widgets de formulario y vista
    $weight = 0;
    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        \Drupal::logger('pre_nanomer_edition')->info('Configuring widget for @name (type: @type)', [
          '@name' => $field_name_full,
          '@type' => $field_config['type'],
        ]);

        // Form display
        if ($field_config['type'] === 'image') {
          $entity_form_display->setComponent($field_name_full, [
            'type' => 'image_image',
            'weight' => $weight++,
            'settings' => [
              'progress_indicator' => 'throbber',
              'preview_image_style' => 'thumbnail',
            ],
            'region' => 'content',
          ]);
          \Drupal::logger('pre_nanomer_edition')->info('Image widget configured: @name', ['@name' => $field_name_full]);
        } elseif ($field_config['type'] === 'email') {
          $entity_form_display->setComponent($field_name_full, [
            'type' => 'email_default',
            'weight' => $weight++,
            'settings' => [
              'placeholder' => '',
            ],
          ]);
          \Drupal::logger('pre_nanomer_edition')->info('Email widget configured: @name', ['@name' => $field_name_full]);
        } else {
          $entity_form_display->setComponent($field_name_full, [
            'type' => $field_config['type'] === 'text_long' ? 'text_textarea' : 'text_textfield',
            'weight' => $weight++,
            'settings' => [
              'rows' => 4,
            ],
          ]);
          \Drupal::logger('pre_nanomer_edition')->info('Text widget configured: @name', ['@name' => $field_name_full]);
        }

        // View display - hide all fields (usaremos plantilla personalizada)
        $entity_view_display->removeComponent($field_name_full);
      } catch (\Exception $e) {
        \Drupal::logger('pre_nanomer_edition')->error('Error configuring widget for @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    $entity_form_display->save();
    $entity_view_display->save();
    
    \Drupal::logger('pre_nanomer_edition')->info('Fields installation completed successfully');
  }

  /**
   * Delete all fields created for the Nanomer Edition 4 content type.
   */
  public static function deleteFields() {
    \Drupal::logger('pre_nanomer_edition')->info('=== STARTING FIELD DELETION ===');

    // Usar la misma definición de campos
    $secciones = [
      'hero_titulo' => ['label' => 'Hero - Título Principal', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'hero_descripcion' => ['label' => 'Hero - Subtítulo', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'objetivo_contenido' => ['label' => 'Objetivo - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Fechas Importantes - campos simples (sin HTML)
      'fechas_titulo' => ['label' => 'Fechas - Título/Fecha', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'fechas_descripcion' => ['label' => 'Fechas - Descripción', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'nota_fechas' => ['label' => 'Nota de Fechas', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      'requisitos_contenido' => ['label' => 'Requisitos - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Documentos - campos simples
      'documentos_titulo' => ['label' => 'Documentos - Título', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'documentos_descripcion' => ['label' => 'Documentos - Descripción', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      
      'procedimiento_contenido' => ['label' => 'Procedimiento - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      'criterios_contenido' => ['label' => 'Criterios - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      'becas_contenido' => ['label' => 'Becas - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Compromisos - campo simple repetible
      'compromisos_descripcion' => ['label' => 'Compromisos - Descripción', 'type' => 'text', 'cardinality' => -1, 'required' => FALSE],
      
      'contacto_email' => ['label' => 'Contacto - Email', 'type' => 'email', 'cardinality' => 1, 'required' => FALSE],
      'contacto_contenido' => ['label' => 'Contacto - Información Adicional', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
    ];

    // PASO 1: Eliminar EntityFormDisplay y EntityViewDisplay (PRIMERO)
    \Drupal::logger('pre_nanomer_edition')->info('STEP 1: Deleting EntityFormDisplay and EntityViewDisplay');
    try {
      $entity_form_display = EntityFormDisplay::load('node.pre_nanomer_edition.default');
      if ($entity_form_display) {
        $entity_form_display->delete();
        \Drupal::logger('pre_nanomer_edition')->info('✓ EntityFormDisplay deleted successfully');
      } else {
        \Drupal::logger('pre_nanomer_edition')->info('EntityFormDisplay not found');
      }

      $entity_view_display = EntityViewDisplay::load('node.pre_nanomer_edition.default');
      if ($entity_view_display) {
        $entity_view_display->delete();
        \Drupal::logger('pre_nanomer_edition')->info('✓ EntityViewDisplay deleted successfully');
      } else {
        \Drupal::logger('pre_nanomer_edition')->info('EntityViewDisplay not found');
      }
    } catch (\Exception $e) {
      \Drupal::logger('pre_nanomer_edition')->error('Error deleting displays: @error', [
        '@error' => $e->getMessage(),
      ]);
    }

    // PASO 2: Eliminar FieldConfig (SEGUNDO)
    \Drupal::logger('pre_nanomer_edition')->info('STEP 2: Deleting FieldConfig instances');
    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        $field = FieldConfig::loadByName('node', 'pre_nanomer_edition', $field_name_full);
        if ($field) {
          $field->delete();
          \Drupal::logger('pre_nanomer_edition')->info('✓ FieldConfig deleted: @name', ['@name' => $field_name_full]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('pre_nanomer_edition')->error('Error deleting FieldConfig @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    // PASO 3: Eliminar FieldStorageConfig (TERCERO)
    \Drupal::logger('pre_nanomer_edition')->info('STEP 3: Deleting FieldStorageConfig definitions');
    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        $field_storage = FieldStorageConfig::loadByName('node', $field_name_full);
        if ($field_storage) {
          $field_storage->delete();
          \Drupal::logger('pre_nanomer_edition')->info('✓ FieldStorage deleted: @name', ['@name' => $field_name_full]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('pre_nanomer_edition')->error('Error deleting FieldStorage @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    \Drupal::logger('pre_nanomer_edition')->info('=== FIELD DELETION COMPLETED ===');
  }

}
