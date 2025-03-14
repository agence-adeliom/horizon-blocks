import Swiper from "swiper";
// @ts-ignore
import {SwiperOptions} from "swiper/types";
import {
  Autoplay,
  Keyboard,
  Navigation,
  Pagination,
  EffectFade,
} from "swiper/modules";
import "swiper/swiper-bundle.css";
import "swiper/css/pagination";
import "swiper/css/autoplay";
import "swiper/css/effect-fade";
import "@styles/components/blocks/arguments.css";

const classes = {
  bullet: "swiper-bullet",
  progress: "step-progress",
  progressInner: "step-progress-inner",
};

document.addEventListener("alpine:init", () => {
  window.Alpine.data("initArgumentsSlider", () => {
    return {
      shown: false,
      index: 0,
      isMobile: false,
      isPlaying: true,
      delay: 6000,
      swiper: {},
      init() {
        this.isMobile = window.matchMedia("(max-width: 1023px)").matches;

        let self = this;

        const swiperParams: SwiperOptions = {
          modules: [Navigation, Keyboard, Pagination, Autoplay, EffectFade],
          init: false,
          effect: "fade",
          fadeEffect: {
            crossFade: true,
          },
          keyboard: {
            enabled: true,
          },
          autoplay: {
            delay: this.delay,
            pauseOnMouseEnter: false,
            disableOnInteraction: false,
          },
          pagination: {
            clickable: true,
            el: this.$refs.pagination,
            type: "bullets",
            bulletClass:
              "swiper-bullet flex w-full gap-3 cursor-pointer overflow-hidden opacity-60 transition-opacity",
            bulletActiveClass: "is-active !opacity-100",
            renderBullet: function (index: number, className: string) {
              const stepTitle = self.$el.querySelector(`.step-title${index}`)
                ? self.$el.querySelector(`.step-title${index}`).innerHTML
                : "";
              const stepDesc = self.$el.querySelector(`.step-desc${index}`)
                ? self.$el.querySelector(`.step-desc${index}`).innerHTML
                : "";

              return (
                '<div tabindex="0" class="' +
                className +
                '">' +
                `<span class="${classes.progress}"><span class="${classes.progressInner}"></span></span>` +
                '<div class="flex flex-col gap-2">' +
                '<p class="font-semibold">' +
                stepTitle +
                "</p>" +
                (stepDesc ? '<p class="p">' + stepDesc + "</p>" : "") +
                "</div>" +
                "</div>"
              );
            },
          },
          on: {
            paginationUpdate: (swiper, paginationEl) => {
              //Trick to cancel animationPlaystate pause on pagination update
              const bulletProgress: NodeListOf<HTMLSpanElement> =
                this.$refs.pagination.querySelectorAll(`.${classes.progress}`);
              if (bulletProgress.length) {
                bulletProgress.forEach((item: HTMLSpanElement) => {
                  const inner: HTMLSpanElement = item.querySelector(
                    `.${classes.progressInner}`
                  );
                  inner.style.animationPlayState = "running";
                });
              }
            },
          },
        };

        const swiper = new Swiper(this.$refs.swiperContainer, swiperParams);

        this.$nextTick(() => {
          swiper.init();
          swiper.autoplay.stop();
        });

        this.$watch("shown", () => {
          this.isMobile = window.matchMedia("(max-width: 1023px)").matches;

          if (!this.isMobile) {
            if (this.shown) {
              swiper.autoplay.start();
            } else {
              swiper.autoplay.stop();
            }
          }
        });

        window.addEventListener("resize", () => {
          this.isMobile = window.matchMedia("(max-width: 1023px)").matches;

          if (!this.isMobile) {
            if (this.shown) {
              swiper.autoplay.start();
            } else {
              swiper.autoplay.stop();
            }
          } else {
            swiper.autoplay.stop();
          }
        });

        this.swiper = swiper;
      },
      handleBulletClick() {
        this.$el
          .querySelectorAll(`.${classes.progress}`)
          .forEach((bullet: HTMLSpanElement) => {
            const inner: HTMLSpanElement = bullet.querySelector(
              `.${classes.progressInner}`
            );
            inner.style.animationPlayState = "running";
          });
      },
      togglePause() {
        const activeProgress: HTMLSpanElement = this.$root.querySelector(
          `.${classes.bullet}.is-active`
        );
        const activeInner: HTMLSpanElement =
          activeProgress &&
          activeProgress.querySelector(`.${classes.progressInner}`);

        if (activeInner) {
          activeInner &&
            (activeInner.style.animationPlayState = this.swiper.autoplay.paused
              ? "running"
              : "paused");
          this.swiper.autoplay.paused
            ? this.swiper.autoplay.resume()
            : this.swiper.autoplay.pause();
          this.isPlaying = !this.swiper.autoplay.paused;
        }
      },
    };
  });
});
