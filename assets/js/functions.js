function scrollToElement(elementId) {
    document.getElementById(elementId).scrollIntoView();
}

function scrollToTop() {
    document.body.scrollIntoView();
}

function submitForm() {
    document.forms['langSel'].submit();
}

// This and the function changeTabs() is based on:
// https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Roles/tab_role#example
window.addEventListener("DOMContentLoaded", () => {
    // Add a click event handler to every tab
    document.querySelectorAll('[role="tab"]')
        .forEach((tab) => {
            tab.addEventListener("click", changeTabs);
        });
  
    // Add a keydown event handler to every tablist
    document.querySelectorAll('[role="tablist"]')
        .forEach((tablist) => {
            tablist.addEventListener("keydown", (e) => {
                // List all tabs in the current tablist
                const myTabs = e.target.parentNode.querySelectorAll(':scope > [role="tab"]');

                // Find index in that list of the calling element
                window.myIndex = -1;
                window.myId = e.target.id;
                myTabs.forEach((p, i) => {
                    if (p.id === window.myId) {
                        window.myIndex = i;
                    }
                });

                // If not found, don't continue
                if (window.myIndex == -1) {
                    return;
                }
      
                // Move left/right
                if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                    // Current element now cannot be reach via Tab key
                    e.target.setAttribute("tabindex", -1);

                    // Move right
                    if (e.key === 'ArrowRight') {
                        window.myIndex++;
                        // If we're at the end, go to the start
                        if (window.myIndex >= myTabs.length) {
                            window.myIndex = 0;
                        }
                    // Move left
                    } else if (e.key === 'ArrowLeft') {
                        window.myIndex--;
                        // If we're at the start, move to the end
                        if (window.myIndex < 0) {
                            window.myIndex = myTabs.length - 1;
                        }
                    }
  
                    // The new tab now can be reached via Tab key and is focused
                    myTabs[window.myIndex].setAttribute("tabindex", 0);
                    myTabs[window.myIndex].focus();
                }

                // Clean up :)
                window.myIndex = -1;
                window.myId = '';
            })
        });
});
  
function changeTabs(e) {
    // Remove all current selected tabs
    e.target.parentNode
      .querySelectorAll(':scope > [aria-selected="true"]')
      .forEach((t) => t.setAttribute("aria-selected", false));
  
    // Set this tab as selected
    e.target.setAttribute("aria-selected", true);
  
    // Hide all tab panels
    e.target.parentNode
      .querySelectorAll(':scope > [role="tabpanel"]')
      .forEach((p) => p.setAttribute("hidden", true));
  
    // Show the selected panel
    e.target.parentNode
      .querySelector(`#${e.target.getAttribute("aria-controls")}`)
      .removeAttribute("hidden");
}
