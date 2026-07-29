/**
 * Barre d'ancres du bloc « Barre de navigation interne (ancres) ».
 *
 * Met en avant le lien correspondant à la section actuellement en haut de la zone lisible.
 *
 * `aria-current` est la seule source de vérité de l'état actif : le CSS s'appuie dessus
 * (cf. styles/blocks/navbar.css), il n'y a pas de classe d'état en double.
 *
 * Sans JS la barre reste utilisable : ce sont de vrais liens de fragment, seule la mise en
 * avant de la section courante manque.
 *
 * Réglage projet : `--navbar-anchors-offset` (hauteur du chrome collant au-dessus de la barre),
 * documenté dans styles/blocks/navbar.css.
 */

/**
 * Fragment seul, sans son `#`.
 *
 * On lit `data-anchor` et non `a.href` : ce dernier renvoie l'URL absolue résolue par le
 * navigateur (`https://…/la-page#mon-ancre`), là où il nous faut le seul identifiant.
 */
const fragmentOf = (link: Element): string => (link.getAttribute('data-anchor') ?? '').replace('#', '');

/**
 * Publie la hauteur de la barre sur :root.
 *
 * Le CSS s'en sert pour dégager les cibles d'ancres du chrome haut de page (`scroll-margin-top`),
 * plutôt que d'y coder en dur une hauteur qui varierait avec la taille du CTA ou des libellés.
 */
const publishHeight = (bar: HTMLElement): void => {
    const measure = (): void => {
        document.documentElement.style.setProperty('--navbar-anchors-height', `${Math.round(bar.offsetHeight)}px`);
    };

    measure();

    if ('ResizeObserver' in window) {
        new ResizeObserver(measure).observe(bar);
    }
};

/** Hauteur du chrome haut de page : décalage fourni par le projet + barre d'ancres. */
const chromeHeight = (): number => {
    const styles = getComputedStyle(document.documentElement);
    const read = (name: string): number => parseFloat(styles.getPropertyValue(name)) || 0;

    return read('--navbar-anchors-offset') + read('--navbar-anchors-height');
};

/** Met en avant le lien de la section qui occupe le haut de la zone lisible, et lui seul. */
const highlightCurrentSection = (): void => {
    const links = Array.from(document.querySelectorAll<HTMLAnchorElement>('[js-anchor]'));

    /*
        Une ancre peut viser une section absente de la page — identifiant renommé, bloc de
        destination supprimé. On ne garde que les cibles qui existent réellement ; les liens
        orphelins restent simplement toujours inactifs.
    */
    const targets = links.map(link => document.getElementById(fragmentOf(link))).filter((target): target is HTMLElement => target !== null);

    if (targets.length === 0) {
        return;
    }

    const activate = (id: string): void => {
        for (const link of links) {
            if (fragmentOf(link) === id) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        }
    };

    let observer: IntersectionObserver | null = null;

    /*
        La zone d'observation est réduite à une ligne de 1px placée juste sous la barre : une
        cible devient active au moment où elle franchit cette ligne, ce qui donne une seule
        section active à la fois, celle qu'on lit effectivement.

        La ligne ne peut pas rester en haut du viewport : elle tomberait derrière le header et
        la barre, qui occupent ce haut d'écran, et aucune section ne la franchirait jamais. Elle
        suit donc la hauteur du chrome, d'où le recalcul à chaque redimensionnement.
    */
    const reobserve = (): void => {
        observer?.disconnect();

        const top = chromeHeight();
        const bottom = Math.max(0, window.innerHeight - top - 1);

        observer = new IntersectionObserver(
            entries => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        activate(entry.target.id);
                    }
                }
            },
            { rootMargin: `-${top}px 0px -${bottom}px 0px`, threshold: 0 },
        );

        for (const target of targets) {
            observer.observe(target);
        }
    };

    reobserve();

    /*
        Le décalage projet peut être une hauteur mesurée en JS, posée après le boot d'Alpine.
        On refait donc le calcul une fois la page complètement chargée, quand les hauteurs
        sont sûres.
    */
    window.addEventListener('load', reobserve, { once: true });

    let frame = 0;
    window.addEventListener(
        'resize',
        () => {
            if (frame) {
                return;
            }

            frame = requestAnimationFrame(() => {
                frame = 0;
                reobserve();
            });
        },
        { passive: true },
    );
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initNavBar', () => {
        return {
            init(): void {
                publishHeight((this as any).$el as HTMLElement);
                highlightCurrentSection();
            },
        };
    });
});
