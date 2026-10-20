import Swiper from "swiper";
import { Autoplay, EffectFade } from "swiper/modules";
import "swiper/swiper-bundle.css";
import "swiper/css/effect-fade";
import "@styles/blocks/arguments.css";

document.addEventListener("alpine:init", () => {
  window.Alpine.data("initArgumentsSlider", () => {
    return {
      index: 0,
      announcement: "",
      shown: false,
      hovered: false,
      focused: false,
      userPaused: false,
      reducedMotion: window.matchMedia("(prefers-reduced-motion: reduce)").matches,
      delay: 6000,
      swiper: null as Swiper | null,

      get running(): boolean {
        return this.shown && !this.hovered && !this.focused && !this.userPaused && !this.reducedMotion;
      },

      init() {
        this.swiper = new Swiper(this.$refs.swiperContainer, {
          modules: [Autoplay, EffectFade],
          effect: "fade",
          fadeEffect: {
            crossFade: true,
          },
          rewind: true,
          autoplay: {
            delay: this.delay,
            disableOnInteraction: false,
          },
          on: {
            init: (swiper) => this.updateSlides(swiper),
            slideChange: (swiper) => this.updateSlides(swiper),
          },
        });

        this.syncAutoplay();
        ["shown", "hovered", "focused", "userPaused"].forEach((key) => this.$watch(key, () => this.syncAutoplay()));
      },

      updateSlides(swiper: Swiper) {
        this.index = swiper.activeIndex;
        swiper.slides.forEach((slide, i) => (slide.inert = i !== swiper.activeIndex));

        if (swiper.autoplay?.paused) {
          swiper.autoplay.stop();
        }
      },

      syncAutoplay() {
        const autoplay = this.swiper?.autoplay;

        if (!autoplay) {
          return;
        }

        if (this.running) {
          autoplay.running ? autoplay.paused && autoplay.resume() : autoplay.start();
        } else if (autoplay.running && !autoplay.paused) {
          autoplay.pause();
        }
      },

      goTo(index: number) {
        this.swiper?.slideTo(index);
      },

      prev() {
        this.swiper?.slidePrev();
        this.announce();
      },

      next() {
        this.swiper?.slideNext();
        this.announce();
      },

      announce() {
        this.announcement = this.swiper?.slides[this.swiper.activeIndex]?.dataset.announcement ?? "";
      },

      togglePause() {
        this.userPaused = !this.userPaused;
      },
    };
  });
});
