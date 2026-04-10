/**
* PHP Email Form Validation - v3.11
* URL: https://bootstrapmade.com/php-email-form/
* Author: BootstrapMade.com
*/
(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach( function(e) {
    e.addEventListener('submit', function(event) {
      event.preventDefault();

      let thisForm = this;

      let action = thisForm.getAttribute('action');
      let recaptcha = thisForm.getAttribute('data-recaptcha-site-key');
      
      if( ! action ) {
        displayError(thisForm, 'The form action property is not set!');
        return;
      }
      const submitButton = thisForm.querySelector('button[type="submit"], input[type="submit"]');
      const loadingElement = thisForm.querySelector('.loading');

      if (loadingElement) {
        loadingElement.textContent = 'Sending subscription...';
        loadingElement.classList.add('d-block');
      }
      if (submitButton) {
        submitButton.disabled = true;
      }

      const errorMessage = thisForm.querySelector('.error-message');
      const sentMessage = thisForm.querySelector('.sent-message');
      if (errorMessage) {
        errorMessage.classList.remove('d-block');
      }
      if (sentMessage) {
        sentMessage.classList.remove('d-block');
      }

      let formData = new FormData( thisForm );

      if ( recaptcha ) {
        if(typeof grecaptcha !== "undefined" ) {
          grecaptcha.ready(function() {
            try {
              grecaptcha.execute(recaptcha, {action: 'php_email_form_submit'})
              .then(token => {
                formData.set('recaptcha-response', token);
                php_email_form_submit(thisForm, action, formData, submitButton, loadingElement);
              })
            } catch(error) {
              displayError(thisForm, error);
            }
          });
        } else {
          displayError(thisForm, 'The reCaptcha javascript API url is not loaded!')
        }
      } else {
        php_email_form_submit(thisForm, action, formData, submitButton, loadingElement);
      }
    });
  });

  function php_email_form_submit(thisForm, action, formData, submitButton, loadingElement) {
    fetch(action, {
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
      if( response.ok ) {
        return response.text();
      } else {
        throw new Error(`${response.status} ${response.statusText} ${response.url}`); 
      }
    })
    .then(data => {
      if (submitButton) {
        submitButton.disabled = false;
      }
      if (loadingElement) {
        loadingElement.classList.remove('d-block');
      }
      const sentMessage = thisForm.querySelector('.sent-message');
      if (data.trim() == 'OK') {
        if (sentMessage && action === 'forms/contact.php') {
          sentMessage.textContent = 'Email sent successfully';
        }
        if (sentMessage) {
          sentMessage.classList.add('d-block');
        }
        thisForm.reset(); 
        setTimeout(() => {
          if (sentMessage) {
            sentMessage.classList.remove('d-block');
          }
        }, 5000);
      } else {
        throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action); 
      }
    })
    .catch((error) => {
      if (submitButton) {
        submitButton.disabled = false;
      }
      displayError(thisForm, error);
    });
  }

  function displayError(thisForm, error) {
    const loadingElement = thisForm.querySelector('.loading');
    if (loadingElement) {
      loadingElement.classList.remove('d-block');
    }
    const errorMessage = thisForm.querySelector('.error-message');
    if (errorMessage) {
      errorMessage.innerHTML = error;
      errorMessage.classList.add('d-block');
    }
  }

})();
