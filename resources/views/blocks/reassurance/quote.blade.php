<x-block :fields="$fields" :block="$block" background="none">
    <div class="grid-12 relative z-10">
        <div class="lg:col-span-8 lg:col-start-3">
            <div class="bg-color-01-100 rounded-card p-card flex flex-col gap-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="23" viewBox="0 0 28 23" fill="none">
                    <path
                            d="M0 23L0 16.4194C0 14.5269 0.330472 12.5591 0.991417 10.5161C1.67239 8.45161 2.59371 6.50538 3.75536 4.67742C4.93705 2.82796 6.25894 1.26882 7.72103 0L12.7682 3.51613C11.5866 5.43011 10.5951 7.43011 9.79399 9.51613C9.01288 11.5806 8.63233 13.8602 8.65236 16.3548L8.65236 23H0ZM15.2318 23L15.2318 16.4194C15.2318 14.5269 15.5622 12.5591 16.2232 10.5161C16.9041 8.45161 17.8255 6.50538 18.9871 4.67742C20.1688 2.82796 21.4907 1.26882 22.9528 0L28 3.51613C26.8183 5.43011 25.8269 7.43011 25.0258 9.51613C24.2446 11.5806 23.8641 13.8602 23.8841 16.3548V23H15.2318Z"
                            fill="#8FAB30"/>
                </svg>
                @isset($fields['title'])
                    <x-typography.heading :fields="$fields['title']" :size="5" class="text-center font-bold"/>
                @endisset
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="23" viewBox="0 0 28 23" fill="none"
                     class="self-end">
                    <path
                            d="M28 0L28 6.58064C28 8.47312 27.6695 10.4409 27.0086 12.4839C26.3276 14.5484 25.4063 16.4946 24.2446 18.3226C23.0629 20.172 21.7411 21.7312 20.279 23L15.2318 19.4839C16.4134 17.5699 17.4049 15.5699 18.206 13.4839C18.9871 11.4194 19.3677 9.13979 19.3476 6.64516L19.3476 -7.56413e-07L28 0ZM12.7682 -1.3316e-06L12.7682 6.58064C12.7682 8.47312 12.4378 10.4409 11.7768 12.4839C11.0959 14.5484 10.1745 16.4946 9.01288 18.3226C7.83119 20.172 6.5093 21.7312 5.04721 23L3.0739e-07 19.4839C1.18169 17.5699 2.1731 15.5699 2.97425 13.4839C3.75537 11.4194 4.13591 9.13978 4.11588 6.64516L4.11588 -2.08802e-06L12.7682 -1.3316e-06Z"
                            fill="#8FAB30"/>
                </svg>
            </div>
        </div>
    </div>
</x-block>