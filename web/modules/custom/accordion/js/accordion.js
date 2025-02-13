(function (Drupal, once) {
  Drupal.behaviors.accordionBehavior = {
    attach: function (context, settings) {
      const animationLength = settings.accordion.length;
      document.documentElement.style.setProperty('--animation-length', `${animationLength}ms`);
      
      once('accordion', '.accordion', context).forEach(function (accordion) {
        const buttons = accordion.querySelectorAll('.accordion-button');
        
        buttons.forEach(function (btn) {
          const panelId = btn.getAttribute('aria-controls');
          const panel = document.getElementById(panelId);

          if (panel) {
            if (btn.getAttribute('aria-expanded') === 'true' || btn.dataset.initiallyOpen === 'true') {
              panel.style.maxHeight = panel.scrollHeight + 'px';
              panel.setAttribute('aria-hidden', 'false');
            }
            else {
              panel.style.maxHeight = '0px';
              panel.setAttribute('aria-hidden', 'true');
            }
          }
        });
        
        function closeAllPanels() {
          buttons.forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'false');
            const panel = document.getElementById(btn.getAttribute('aria-controls'));
            if (panel) {
              panel.style.maxHeight = '0px';
              panel.setAttribute('aria-hidden', 'true');
            }
          });
        }
        
        // Keyboard navigation
        buttons.forEach(function (btn) {
          const panelId = btn.getAttribute('aria-controls');
          const panel = document.getElementById(panelId);
          
          btn.addEventListener('click', function (e) {
            e.preventDefault();
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            closeAllPanels();
            if (!isOpen) {
              btn.setAttribute('aria-expanded', 'true');
              panel.setAttribute('aria-hidden', 'false');
              panel.style.maxHeight = panel.scrollHeight + 'px';
            }
          });
          
          btn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
              e.preventDefault();
              btn.click();
            }
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
              e.preventDefault();
              const btnArray = Array.from(buttons);
              let index = btnArray.indexOf(btn);
              if (e.key === 'ArrowDown') {
                index = (index + 1) % btnArray.length;
              }
              else if (e.key === 'ArrowUp') {
                index = (index - 1 + btnArray.length) % btnArray.length;
              }
              btnArray[index].focus();
            }
          });
        });
        
        // Deep linking
        var hash = window.location.hash;
        if (hash) {
          var targetId = hash.substring(1);
          var targetPanel = accordion.querySelector('#' + targetId);
          if (targetPanel && targetPanel.classList.contains('accordion-body')) {
            console.log("[Accordion] Deep linking target found:", targetId);
            var targetButton = accordion.querySelector('[aria-controls="' + targetId + '"]');
            if (targetButton) {
              closeAllPanels();
              targetButton.setAttribute('aria-expanded', 'true');
              targetPanel.setAttribute('aria-hidden', 'false');
              targetPanel.style.maxHeight = targetPanel.scrollHeight + 'px';
              targetPanel.scrollIntoView({ behavior: 'smooth' });
            }
          }
        }
      });
    }
  };
})(Drupal, once);