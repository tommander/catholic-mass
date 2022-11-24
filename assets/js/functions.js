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
    const theEmail = document.getElementById('FEEDBACK_EMAIL');
    const theDescription = document.getElementById('FEEDBACK_DESCRIPTION');
    const theRating = document.getElementById('FEEDBACK_RATING');
    if (theToggle === null || theForm === null || theThanks === null || theEmail === null || theDescription === null || theRating === null) {
        return;
    }

    let dataBody = new FormData(document.forms['feedbackForm']);
    let data = {
        method: 'POST',
        body: dataBody
    }
    console.log(fetch('http://localhost/mass/feedback.php', data));

    theForm.setAttribute('hidden', 'hidden');
    theToggle.setAttribute('hidden', 'hidden');
    theThanks.removeAttribute('hidden');
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
