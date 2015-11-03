<style media="screen">
    canvas {
        max-width: 100%;
        height: auto;
    }
</style>
<div class="uk-form" riot-view>

    <ul class="uk-breadcrumb">
        @render('cocopi:views/partials/subnav.php')
        <li each="{p in parents}" data-uk-dropdown>
            <a href="@route('/cocopi/page'){ p.relpath }"><i class="uk-icon-home" if="{p.isRoot}"></i> { p.meta.title.substring(0, 15) }</a>
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/cocopi/pages'){p.relpath}">@lang('Sub Pages')</a></li>
                    <li><a href="@route('/cocopi/files'){p.relpath}">@lang('Files')</a></li>
                </ul>
            </div>
        </li>
        <li data-uk-dropdown>
            <a href="@route('/cocopi/page'.$page->relpath())"><i class="uk-icon-home" if="{page.isRoot}"></i> { page.meta.title.substring(0, 15) }</a>
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/cocopi/files'){page.relpath}">@lang('Files')</a></li>
                </ul>
            </div>
        </li>
        <li><span class="uk-text-primary">@lang('Pages') <span show="{children.length}">({children.length})</span></span></li>
    </ul>

    <div class="uk-margin" if="{children.length}">
        <a class="uk-button uk-button-primary" onclick="{ createPage }" if="{ type.subpages !== false }">@lang('Create Page')</a>

        <div class="uk-form-icon uk-form uk-text-muted uk-float-right">

            <i class="uk-icon-filter"></i>
            <input class="uk-form-large uk-form-blank" type="text" name="txtfilter" placeholder="@lang('Filter pages...')" onkeyup="{ update }">

        </div>
    </div>

    <div name="container" class="uk-grid uk-grid-match uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-sortable" show="{children.length}">

        <div class="uk-grid-margin" each="{child,idx in children}" show="{ parent.infilter(child) }" data-path="{ child.path }">
            <div class="uk-panel uk-panel-box uk-panel-card">
                <div class="uk-flex">
                    <span class="uk-margin-small-right" data-uk-dropdown>
                        <i class="uk-icon-file-text-o uk-text-{ child.visible ? 'success':'danger' }"></i>
                        <div class="uk-dropdown">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Browse')</li>
                                <li><a href="@route('/cocopi/pages'){child.relpath}">@lang('Sub Pages')</a></li>
                                <li><a href="@route('/cocopi/files'){child.relpath}">@lang('Files')</a></li>
                                <li class="uk-nav-divider"></li>
                                <li><a onclick="{ parent.remove }" data-path="{ child.path }">@lang('Delete')</a></li>
                            </ul>
                        </div>
                    </span>
                    <a class="uk-flex-item-1 uk-text-truncate" href="@route('/cocopi/page'){ child.relpath }">{ child.meta.title }</a>
                </div>
                <div class="uk-position-relative">
                    <canvas width="600" height="400"></canvas>
                    <a class="uk-position-cover" href="@route('/cocopi/page'){ child.relpath }"></a>
                </div>
                <div class="uk-margin-small-top uk-text-small uk-text-muted">
                    { child.type }
                </div>
            </div>
        </div>
    </div>

    <div class="uk-margin-large-top uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{!children.length}">

        <div class="">

            <h3>{ page.meta.title }</h3>

            <p>
                { App.i18n.get('This page has no sub-pages.') }
            </p>
            <p if="{ type.subpages !== false }">
                <a class="uk-button uk-button-large uk-button-primary" onclick="{ createPage }">@lang('Create one')</a>
            </p>
        </div>
    </div>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.page     = {{ json_encode($page->toArray()) }};
        this.children = {{ json_encode($page->children()->sorted()->toArray()) }};
        this.parents  = {{ json_encode(array_reverse($page->parents()->toArray())) }};
        this.type     = {{ json_encode($type) }};

        this.on('mount', function() {

            var sortable = UIkit.sortable(App.$('[name="container"]'), {animation: true}).element.on("change.uk.sortable", function(e, sortable, ele) {

                var order = [];

                sortable.element.children().each(function(index){
                    order.push(this.getAttribute('data-path'));
                });

                App.request('/cocopi/utils/updatePagesOrder', {order: order}).then(function(){
                    App.ui.notify("Pages reordered", "success");
                });
            });
        });

        createPage(e) {

            var options = {};

            if (this.type && this.type.subpages) {

                var subpages = Array.isArray(this.type.subpages) ? this.type.subpages : [this.type.subpages];

                options.types = {};

                subpages.forEach(function(type) {
                    if (COCOPI_PAGE_TYPES[type]) {
                        options.types[type] = COCOPI_PAGE_TYPES[type];
                    }
                });
            }


            cocopi.createPage(this.page.isRoot ? '/':this.page.contentdir, options);
        }

        remove(e) {

            var path = e.item.child.path;

            App.ui.confirm("Are you sure?", function() {

                App.request('/cocopi/utils/deletePage', {path:path}).then(function(data) {

                    App.ui.notify("Page removed", "success");

                    $this.children.splice(e.item.idx, 1);
                    $this.update();
                });
            });
        }

        infilter(page, value, name) {

            if (!this.txtfilter.value) {
                return true;
            }

            value = this.txtfilter.value.toLowerCase();
            name  = page.meta.title;

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
