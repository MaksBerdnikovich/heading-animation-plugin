document.addEventListener('theGemHeading.startAnimation', function(event) {
    const element = event.target;

    let rotatingItem = element.querySelector('.thegem-heading-rotating');
    if (typeof(rotatingItem) != 'undefined' && rotatingItem != null) {
        document.fonts.ready.then(()=>{
            theGemHeadingRotatingText(rotatingItem);
        });
    }
});

function theGemHeadingRotatingText(element) {
    if (!element) return;

    let items = Array.from(element.getElementsByClassName('thegem-heading-rotating-text'));
    if (items.length === 0) return;

    let duration = element.dataset.duration !== undefined ? parseInt(element.dataset.duration) : 1400;
    let animation = element.dataset.animation;
    let current = items[0];

    if (animation === 'fade') {
        let maxWidth = Math.max(...items.map((item)=>item.clientWidth));
        items.forEach((item)=>item.style.width = maxWidth + 'px');

        setInterval(()=>{
            let next = current.nextElementSibling !== null ? current.nextElementSibling : element.childNodes[0];
            current.style.opacity = 0;

            setTimeout(()=>{
                current.style.position = 'absolute';
                next.style.position = 'relative';
                next.style.opacity = 1;
                current = next;
            }, 700);

        }, 1000 + duration);
    } else {
        element.style.width = element.clientWidth+'px';
        current.style.width = element.clientWidth+'px';
        items.forEach((item)=>item.dataset.width = item.clientWidth);

        setInterval(()=>{
            let next = current.nextElementSibling !== null ? current.nextElementSibling : element.childNodes[0];
            let nextWidth = next.dataset.width;
            let currentWidth = current.dataset.width;

            element.style.width = nextWidth + 'px';
            current.style.width = '1px';
            current.style.opacity = 0;
            next.style.width = '1px';

            setTimeout(()=>{
                current.style.position = 'absolute';
                current.style.width = currentWidth + 'px';

                next.style.position = 'relative';
                next.style.opacity = 1;
                next.style.width = nextWidth + 'px';

                current = next;
            }, 500);

        }, 1000 + duration);
    }
}