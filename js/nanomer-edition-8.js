(function (Drupal) {
  'use strict';

  Drupal.behaviors.nanomerEdition4 = {
    attach: function (context) {
      // Smooth interactions for form fields
      var textareas = context.querySelectorAll('.node--nanomer-edition-4 textarea');
      textareas.forEach(function (textarea) {
        // Auto-expand textarea as content grows
        function autoResize() {
          textarea.style.height = 'auto';
          textarea.style.height = (textarea.scrollHeight + 10) + 'px';
        }

        textarea.addEventListener('input', autoResize);
        textarea.addEventListener('keydown', autoResize);
      });

      // Enhanced form validation
      var nodeForm = context.querySelector('form.node-form');
      if (nodeForm && nodeForm.classList.contains('nanomer_edition_4-form')) {
        nodeForm.addEventListener('submit', function (e) {
          var requiredFields = nodeForm.querySelectorAll('[required]');
          var isValid = true;

          requiredFields.forEach(function (field) {
            if (!field.value || field.value.trim() === '') {
              field.classList.add('form-error');
              isValid = false;
            } else {
              field.classList.remove('form-error');
            }
          });

          if (!isValid) {
            e.preventDefault();
            console.warn('Please fill all required fields');
          }
        });
      }

      // Preview functionality for text fields
      var previewButton = context.querySelector('.nanomer-edition-4-preview-btn');
      if (previewButton) {
        previewButton.addEventListener('click', function () {
          var form = this.closest('form');
          var formData = new FormData(form);
          
          // Aquí puedes agregar lógica para mostrar una vista previa
          console.log('Preview requested');
        });
      }
    }
  };
})(Drupal);
