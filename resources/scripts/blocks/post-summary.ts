const PostSummary = {
    init: undefined,
    getPostSummary: undefined,
    selector: '.summary-list',
    initElt: undefined,
    eltSelector: '.summary-elt',
    associations: [],
};

let instance: HTMLElement = null;

PostSummary.getPostSummary = () => {
    if (null === instance) {
        instance = document.querySelector(PostSummary.selector);
    }

    return instance;
}

PostSummary.initElt = (elt: HTMLElement) => {
    const listElts: Element[] = Array.from(elt.querySelectorAll(PostSummary.eltSelector));

    listElts.forEach(listElt => {
        let title: string = null;

        if (listElt.hasAttribute('data-title')) {
            title = listElt.getAttribute('data-title');
        } else {
            title = listElt.textContent.trim();
        }

        const xpath = `//*[normalize-space(text())="${title}"]`;
        let contentTitle = null;
        const nodeSnapshot = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

        for (let i = 0; i < nodeSnapshot.snapshotLength; i++) {
            const item = nodeSnapshot.snapshotItem(i);

            if (item.classList.contains('heading')) {
                contentTitle = item;
                break;
            }
        }

        if (contentTitle) {
            PostSummary.associations.push({
                content: contentTitle,
                summary: listElt,
            })
            listElt.addEventListener('click', () => {
                // Scroll to contentTitle
                window.scrollTo({
                    top: contentTitle.getBoundingClientRect().top + window.scrollY,
                    behavior: 'smooth'
                });
            });
        }
    })
}

PostSummary.init = () => {
    if (PostSummary.getPostSummary()) {
        PostSummary.initElt(PostSummary.getPostSummary());
    }
};

PostSummary.init();
