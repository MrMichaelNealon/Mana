<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        
        <title>
            <?php echo APP_TITLE; ?>
        </title>
    </head>
    <body>
        <div id="outer">
            <div id="inner">
                @partial partials.header
                
                @inner
            </div>
        </div>

        <div id="overlay">
        </div>

        <div id="dialog">
        </div>

        <div id="image-browser">
            <div id="image-browser-inner">
                @partial partials.imagegallery
            </div>
            <div id="image-browser-cancel" class="browser-option" style="width: auto;">
                Cancel
            </div>
        </div>

        <script>
            $(document).ready(function() {
                let _toggle = _Toggle();
                let _header = _Header();
                let _sidebar = _Sidebar(_toggle);
                let _user_form = _UserForm();
                
                let _overlay = _Overlay();
                let _image_browser = _ImageBrowser();


                function cancelImageDelete() {
                    _overlay.hideDialog();
                }

                function stripPath(path) {
                    let _out = "";

                    for (let c = (path.length - 1); c >= 0; c--) {
                        if (path.substr(c, 1) == '/' || path.substr(c, 1) == '\\')
                            break;
                        _out = path.substr(c, 1) + _out;
                    }

                    return _out;
                }

                $(".image-delete").on("click", function() {
                    let _img = $(this).attr("name");

                    let _html = "\
                        <div class='dialog-section'>\
                            Delete <b>" + _img + "</b>?\
                        </div>\
                        <div class='dialog-section'>\
                            <div id='confirm-image-delete' class='dialog-option'>Delete</div>\
                            <div id='cancel-image-delete' class='dialog-option'>Cancel</div>\
                        </div>\
                    ";

                    _overlay.showDialog(_html);

                    $("#confirm-image-delete").on("click", function() {
                        window.location.href = '/deleteimage/' + stripPath(_img);
                    });

                    $("#cancel-image-delete").on("click", function() {
                        cancelImageDelete();
                    });
                });

                $("#project-title,#project-body").on("click", function() {
                    $(this).attr("contenteditable", "true");
                    $(this).css("background", "#FFF");
                });

                $(document).on('click', function (event) {
                    if (
                        ! $(event.target
                        ).closest('#project-title').length &&
                        ! $(event.target).closest('#project-body').length
                    ) {
                        $("#project-title,#project-body").attr("contenteditable", "false");
                        $("#project-title,#project-body").css("background", "none");
                    }
                });
    

                if (typeof(_project_paths) === 'string') {
                    _image_browser.parsePaths(_project_paths, _project_texts);
                
                    if (typeof(_project_layout) === 'string') {
                        if (_project_layout == "project-regular")
                            $(".project-image").removeClass("project-image-half");
                        else
                            $(".project-image").addClass("project-image-half");
                    }
                $(".project-layout").css("border", "none");
                $("#" + _project_layout).css("border", "2px solid #EC2555");

                }

                $("#add-image").on("click", function() {
                    _image_browser.selectImage();
                });

                $("#image-browser-cancel").on("click", function() {
                    _image_browser.closeBrowser();
                });

                // $(".project-image").on("click", function() {
                //     _image_browser.addImage($(this).attr("src"));
                // });

                $('.image-selector').on("click", function() {
                    _image_browser.addImage($(this).attr("src"));
                    _image_browser.showImages();
                    _image_browser.closeBrowser();
                });

                $("#submit-project").submit(function(e) {
                    _image_browser.submitProject(e);
                });

                $('.project-image-delete').on("click", function() {
                    let _id = $(this).attr("id");
                    _image_browser.removeImage(parseInt(_id.substr(14)));
                });
                        
                $('.project-nav a').on("mouseleave", function() {
                    if ($(this).attr("id") === "project-nav-" + __nav_cat)
                        return;
                    
                    $(this).stop().animate({
                        "opacity": "0.90"
                    }, 200, "linear");
                });

                $('.project-nav a').on("mouseenter", function() {
                    if ($(this).attr("id") === "project-nav-" + __nav_cat)
                        return;
                    
                    $(this).stop().animate({
                        "opacity": "0.99"
                    }, 200, "linear");
                });    
            });
        </script>
    </body>
</html>