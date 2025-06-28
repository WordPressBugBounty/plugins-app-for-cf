
/**
 * Create the CloudflareAppNotice namespace
 * @package CloudflareApp
 */

let CloudflareAppNotice = {};

{
    CloudflareAppNotice.Notice = function() { this.__construct(); };
    CloudflareAppNotice.Notice.prototype =
    {
        __construct: function()
        {
            document.addEventListener("DOMContentLoaded", () => {
                document.addEventListener('click', (event) => {
                    const target = event.target;

                    if (target.classList.contains('notice-dismiss'))
                    {
                        let element = target.parentElement;

                        while (element && (!element.classList.contains('notice') || !element.classList.contains('is-dismissible')))
                        {
                            element = element.parentElement;
                        }

                        if (element.dataset.dismiss_key)
                        {
                            this.click(element.dataset.dismiss_key);
                        }
                    }
                });
            }, false)
        },

        click: function(key)
        {
			fetch(ajaxurl, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "app-for-cf_notice_dismiss",
					key: key
				})
			});
        },

    };

    CloudflareAppNotice._Notice = new CloudflareAppNotice.Notice();
}