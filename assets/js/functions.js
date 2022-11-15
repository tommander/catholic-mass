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
