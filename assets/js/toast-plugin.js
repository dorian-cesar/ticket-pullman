// Toast Plugin
(function ($) {

    var defaults = {
        title: "",
        message: "",
        type: "success", // success, error, info, warning
        duration: 5000
    };

    var createToastContainer = function () {
        var toastContainer = $("<div>").addClass("custom-toast-container").attr("id", "customToastContainer");
        $("body").append(toastContainer);
        return toastContainer;
    };

    var createToast = function (settings) {
        var svgIcon;
        var title;
        switch (settings.type) {
            case 'success':
                title = 'Éxito';
                svgIcon = '<i class="bi bi-check-circle-fill"></i>';
                break;
            case 'error':
                title = 'Error';
                svgIcon = '<i class="bi bi-x-circle-fill"></i>';
                break;
            case 'info':
                title = 'Información';
                svgIcon = '<i class="bi bi-info-circle-fill"></i>';
                break;
            case 'warning':
                title = 'Advertencia';
                svgIcon = '<i class="bi bi-exclamation-circle-fill"></i>';
                break;
            default:
                title = 'Éxito';
                svgIcon = '<i class="bi bi-check-circle-fill"></i>';
        }

        var toastHTML = `
            <div class="custom-toast ${settings.type}">
                <div class="icon-container">
                    ${svgIcon}
                </div>
                <div class="content-container">
                    <p class="title">${title}</p>
                    <p class="message">${settings.message}</p>
                </div>
                <button class="close-button">&times;</button>
            </div>
        `;

        var toast = $(toastHTML).appendTo("#customToastContainer");

        // Set timeout to automatically close the toast
        setTimeout(function () {
            closeCustomToast(toast);
        }, settings.duration);

        // Bind click event to close button
        toast.find('.close-button').click(function () {
            closeCustomToast(toast);
        });

        // Apply animation class after a short delay to trigger animation
        setTimeout(function () {
            toast.addClass("show");
        }, 100);

        return toast; // Enable chaining
    };

    var closeCustomToast = function (toast) {
        toast.removeClass("show");
        setTimeout(function () {
            toast.remove();
            if ($(".custom-toast-container .custom-toast").length === 0) {
                $("#customToastContainer").remove();
            }
        }, 300);
    };

    $.toast = function (options) {
        var settings = $.extend({}, defaults, options);

        if ($("#customToastContainer").length === 0) {
            createToastContainer();
        }

        return createToast(settings);
    };
})(jQuery);
