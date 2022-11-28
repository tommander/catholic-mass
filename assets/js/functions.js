function markStar(spanId) {
    for (let i = 1; i <= 5; i++) {
        const theSpan = document.getElementById('RAT'+i);
        if (theSpan === null) {
            continue;
        }
        if (i <= spanId) {
            theSpan.classList.add('starSelect');
        } else {
            theSpan.classList.remove('starSelect');
        }
    }

    const theInput = document.getElementById('FEEDBACK_RATING');
    if (theInput === null) {
        return;
    }
    theInput.value = spanId;
}

function toggleFeedback() {
    const theElement = document.getElementById('FEEDBACK_FORM');
    if (theElement === null) {
        return;
    }
    if (theElement.hasAttribute('hidden')) {
        theElement.removeAttribute('hidden');
    } else {
        theElement.setAttribute('hidden', 'hidden');
    }
}

function submitFeedback() {
    const theToggle = document.getElementById('FEEDBACK_TOGGLE');
    const theForm = document.getElementById('FEEDBACK_FORM');
    const theThanks = document.getElementById('FEEDBACK_THANKS');
    const theError = document.getElementById('FEEDBACK_ERROR');
    const theErrorDetail = document.getElementById('FEEDBACK_ERROR_DETAIL');
    const theEmail = document.getElementById('FEEDBACK_EMAIL');
    const theDescription = document.getElementById('FEEDBACK_DESCRIPTION');
    const theRating = document.getElementById('FEEDBACK_RATING');
    const theRules = document.getElementById('FEEDBACK_RULES');
    const theValidation = document.getElementById('FEEDBACK_VALIDATION');
    if (theToggle === null || theForm === null || theThanks === null || theError === null || theErrorDetail === null || theEmail === null || theDescription === null || theRating === null || theRules === null || theValidation === null) {
        return;
    }

    theValidation.innerHTML = '';
    var validForm = true;
    let emailPattern = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/
    if (!emailPattern.test(theEmail.value)) {
        theValidation.innerHTML += 'Incorrect e-mail address<br>';
        validForm = false;
    }

    if (theDescription.value.length == 0) {
        theValidation.innerHTML += 'Empty description<br>';
        validForm = false;
    }

    if (!theRules.checked) {
        theValidation.innerHTML += 'Please read and check the rules<br>';
        validForm = false;
    }

    if (!validForm) {
        return;
    }

    let dataBody = new FormData(document.forms['feedbackForm']);
    let data = {
        method: 'POST',
        body: dataBody
    }
    fetch(window.baseUrl+'feedback.php', data)
        .then((response) => response.json())
        .then((response) => {
            theForm.setAttribute('hidden', 'hidden');
            theToggle.setAttribute('hidden', 'hidden');
            console.log(response);
            if (response.success) {
                theThanks.removeAttribute('hidden');
            } else {
                theErrorDetail.innerText = 'Code #'+response.code+': '+response.message;
                theError.removeAttribute('hidden');
            }
        })
        .catch((response) => {
            theForm.setAttribute('hidden', 'hidden');
            theToggle.setAttribute('hidden', 'hidden');
            theErrorDetail.innerText = 'Unknown error with feedback submission.';
            theError.removeAttribute('hidden');
        });
}

function scrollToElement(elementId) {
    const theElement = document.getElementById(elementId);
    if (theElement === null) {
        return;
    }
    theElement.scrollIntoView();
}

function scrollToTop() {
    document.body.scrollIntoView();
}

function submitForm() {
    document.forms['langSel'].submit();
}

function isNode(what) {
    return (typeof what == "object" && what instanceof Node);
}

function tabClick(obj) {
    if (! isNode(obj)) {
        alert('Error1');
        return;
    }
    let objParent = obj.parentElement;
    if (! isNode(objParent)) {
        alert('Error2');
        return;
    }
    let objGrandParent = objParent.parentElement;
    if (! isNode(objGrandParent)) {
        alert('Error3');
        return;
    }

    for (let i = 0; i < objParent.children.length; i++) {
        let objChild = objParent.children[i];
        if (! objChild.classList.contains('option')) {
            continue;
        }
        objChild.classList.remove('optionSelected');
    }
    let objClassName = obj.className;
    obj.classList.add('optionSelected');

    var objContentParent = null;
    for (let i = 0; i < objGrandParent.children.length; i++) {
        let objGrandParentChild = objGrandParent.children[i];
        if (! objGrandParentChild.classList.contains('choiceContent')) {
            continue;
        }
        var objContentParent = objGrandParentChild;
        break;
    }

    if (! isNode(objContentParent)) {
        alert('Error4');
        return;
    }

    for (let i = 0; i < objContentParent.children.length; i++) {
        let objContentParentChild = objContentParent.children[i];
        if (objContentParentChild.className == objClassName) {
            objContentParentChild.removeAttribute('hidden');
            continue;
        }
        objContentParentChild.setAttribute('hidden', 'hidden');
    }

}
