import Swiper from "swiper";
import { Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";

document.addEventListener("alpine:init", () => {
  window.Alpine.data("initReviewsSlider", () => {
    return {
      swiper: null as Swiper | null,
      locked: false,

      init() {
        this.swiper = new Swiper(this.$refs.swiperContainer, {
          modules: [Pagination],
          slidesPerView: 1.2,
          spaceBetween: 12,
          rewind: true,
          centerInsufficientSlides: true,
          // Puces purement visuelles (non cliquables, masquées aux lecteurs d'écran) : la navigation passe par les boutons
          pagination: {
            el: this.$refs.swiperPagination,
            type: "bullets",
            clickable: false,
            lockClass: "hidden",
            hiddenClass: "hidden",
            bulletActiveClass: "bg-primary",
            bulletClass: "bg-neutral-300 w-6 h-1 rounded-full inline-block transition-colors",
          },
          breakpoints: {
            768: {
              slidesPerView: 2.2,
            },
            1024: {
              spaceBetween: 24,
              slidesPerView: 2.6,
            },
            1280: {
              slidesPerView: 3,
            },
          },
          on: {
            init: (swiper) => (this.locked = swiper.isLocked),
            lock: () => (this.locked = true),
            unlock: () => (this.locked = false),
          },
        });
      },
    };
  });
});
