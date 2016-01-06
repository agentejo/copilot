<div class="uk-form" riot-view>

    <ul  class="uk-breadcrumb">
        @render('copilot:views/partials/subnav.php')
        <li each="{p in parents}" data-uk-dropdown>
            <a href="@route('/copilot/page'){ p.relpath }"><i class="uk-icon-home" if="{p.isRoot}"></i> { p.meta.title.substring(0, 15) }</a>
            <div class="uk-dropdown">

                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/copilot/pages'){p.relpath}">@lang('Sub Pages')</a></li>
                    <li><a href="@route('/copilot/files'){p.relpath}">@lang('Files')</a></li>
                </ul>

                <div class="uk-margin" if="{ copilot.getType(p.type).subpages !== false }">
                    <strong class="uk-text-small">Sub pages</strong>
                    <cp-pagejumplist dir="{p.dir}"></cp-pagejumplist>
                </div>

            </div>
        </li>
        <li data-uk-dropdown>
            <a href="@route('/copilot/page'.$page->relpath())"><i class="uk-icon-home" if="{page.isRoot}"></i> { page.meta.title.substring(0, 15) }</a>
            <div class="uk-dropdown">

                <div class="uk-margin" if="{ copilot.getType(page.type).subpages !== false }">
                    <strong class="uk-text-small">Sub pages</strong>
                    <cp-pagejumplist dir="{page.dir}"></cp-pagejumplist>
                </div>

                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/copilot/pages'){page.relpath}">@lang('Sub Pages')</a></li>
                </ul>
            </div>
        </li>
        <li>
            <span class="uk-text-primary">Files <span class="uk-badge" show="{files && files.length}">{files.length}</span></span>
            <span name="loadprogress" class="uk-hidden"><i class="uk-icon-refresh uk-icon-spin"></i></span>
        </li>
    </ul>

    <div class="uk-margin" show="{files && files.length}">

        <span class="uk-button uk-button-primary uk-margin-small-right uk-form-file">
            <input class="js-upload-select-one" type="file" multiple="true" title="">
            <i class="uk-icon-upload"></i> @lang('Upload files')
        </span>

        <div class="uk-form-icon uk-form uk-text-muted uk-float-right">

            <i class="uk-icon-filter"></i>
            <input class="uk-form-large uk-form-blank" type="text" name="txtfilter" placeholder="@lang('Filter files...')" onkeyup="{ update }">

        </div>
    </div>

    <div name="uploadprogress" class="uk-margin uk-hidden">
        <div class="uk-progress">
            <div name="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
        </div>
    </div>


    <div name="container" class="uk-grid uk-grid-match uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-sortable" show="{files && files.length}">

        <div class="uk-grid-margin" each="{file in files}" show="{ parent.infilter(file) }" data-path="{ file.path }">
            <div class="uk-panel uk-panel-box uk-panel-card">

                <div class="uk-cover-background uk-position-relative">
                    <canvas class="uk-responsive-width uk-display-block" width="400" height="300" if="{!file.isImage}"></canvas>
                    <cp-thumbnail src="{file.url}" width="400" height="300" if="{file.isImage}"></cp-thumbnail>
                    <a class="uk-position-cover uk-text-muted uk-flex uk-flex-middle uk-flex-center" href="@route('/copilot/file'){ file.relpath }">
                        <div class="uk-text-large uk-text-center" if="{!file.isImage}">
                            <p>{ file.ext.toUpperCase() }</p>
                        </div>
                    </a>
                </div>

                <div class="uk-margin-top uk-flex">

                    <span class="uk-margin-small-right" data-uk-dropdown>
                        <a><i class="uk-icon-{ copilot.getFileIconCls(file.filename) }"></i></a>
                        <div class="uk-dropdown uk-dropdown-close">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li><a onclick="{ parent.rename }">Rename</a></li>
                                <li class="uk-nav-divider"></li>
                                <li><a onclick="{ parent.remove }">Delete</a></li>
                            </ul>
                        </div>
                    </span>

                    <a class="uk-flex-item-1 uk-text-truncate" href="@route('/copilot/file'){ file.relpath }">{ file.filename }</a>

                </div>
                <div class="uk-text-small uk-text-muted uk-margin-small-top">
                    { file.fsize }
                </div>
            </div>
        </div>
    </div>

    <div class="uk-margin-large-top uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" show="{files && !files.length}">

        <div class="">

            <h3>{ page.meta.title }</h3>

            <p>
                { App.i18n.get('This page has no files.') }
            </p>

            <p>
                <span class="uk-button uk-button-large uk-button-primary uk-margin-small-right uk-form-file">
                    <input class="js-upload-select-two" type="file" multiple="true" title="">
                    <i class="uk-icon-upload"></i> @lang('Upload files')
                </span>
            </p>
        </div>
    </div>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.page    = {{ json_encode($page->toArray()) }};
        this.parents = {{ json_encode(array_reverse($page->parents()->toArray())) }};
        this.files   = null;

        this.currentpath = App.Utils.dirname($this.page.relpath);

        this.on('mount', function(){

            this.loadPath();

            // handle uploads
            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {

                    action: App.route('/media/api'),
                    params: {"cmd":"upload"},
                    type: 'json',
                    before: function(options) {
                        options.params.path = $this.currentpath;
                    },
                    loadstart: function() {
                        $this.uploadprogress.classList.remove('uk-hidden');
                    },
                    progress: function(percent) {

                        percent = Math.ceil(percent) + '%';

                        $this.progressbar.innerHTML   = '<span>'+percent+'</span>';
                        $this.progressbar.style.width = percent;
                    },
                    allcomplete: function(response) {

                        $this.uploadprogress.classList.add('uk-hidden');

                        if (response && response.failed && response.failed.length) {
                            App.ui.notify("File(s) failed to uploaded.", "danger");
                        }

                        if (response && response.uploaded && response.uploaded.length) {
                            App.ui.notify("File(s) uploaded.", "success");
                            $this.loadPath();
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }

                    }
                },

                uploadselectone = UIkit.uploadSelect(App.$('.js-upload-select-one', $this.root)[0], uploadSettings),
                uploadselecttwo = UIkit.uploadSelect(App.$('.js-upload-select-two', $this.root)[0], uploadSettings),
                uploaddrop      = UIkit.uploadDrop('body', uploadSettings);

                UIkit.init(this.root);

                var sortable = UIkit.sortable(App.$('[name="container"]'), {animation: true}).element.on("change.uk.sortable", function(e, sortable, ele) {

                    var order = [];

                    sortable.element.children().each(function(index){
                        order.push(this.getAttribute('data-path'));
                    });

                    App.request('/copilot/utils/updateResourcesOrder', {order: order}).then(function(){
                        App.ui.notify("Files reordered", "success");
                    });
                });
            });
        });

        loadPath() {

            $this.loadprogress.classList.remove('uk-hidden');

            App.request('/copilot/utils/getPageResources', {path:this.page.path}).then(function(data) {

                setTimeout(function(){

                    $this.loadprogress.classList.add('uk-hidden');
                    $this.files = data || [];
                    $this.update();

                }, 100);

            });

        }

        rename(e, item) {

            e.stopPropagation();

            item = e.item.file;

            App.ui.prompt("Please enter a name:", item.filename, function(name){

                if (name!=item.filename && name.trim()) {

                    App.request('/copilot/utils/renameResource', {path:item.path, name:name.trim()}).then(function(data) {

                        item.path = item.path.replace(item.filename, name);
                        item.url = item.url.replace(encodeURI(item.filename), encodeURI(name));
                        item.relpath = item.relpath.replace(item.filename, name);
                        item.filename = name;

                        $this.update();
                    });
                }
            });
        }

        remove(e, item, index) {

            e.stopPropagation();

            item = e.item.file;

            App.ui.confirm("Are you sure?", function() {

                App.request('/copilot/utils/deleteResource', {path:item.path}).then(function(data) {

                    index = $this.files.indexOf(item);
                    $this.files.splice(index, 1);
                    App.ui.notify("File deleted", "success");
                    $this.update();
                });
            });
        }

        function requestapi(data, fn, type) {

            data = Object.assign({"cmd":""}, data);

            App.request('/media/api', data).then(fn);
        }

        infilter(file, value, name) {

            if (!this.txtfilter.value) {
                return true;
            }

            value = this.txtfilter.value.toLowerCase();
            name  = file.filename;

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
