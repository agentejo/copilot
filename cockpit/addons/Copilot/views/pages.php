<div class="uk-form" riot-view>

    <ul class="uk-breadcrumb">
        @render('copilot:views/partials/subnav.php')
        <li each="{p in parents}" data-uk-dropdown>
            <a href="@route('/copilot/page'){ p.relpath }"><i class="uk-icon-home" if="{p.isRoot}"></i> { p.meta.title.substring(0, 15) }</a>
            <div class="uk-dropdown" if="{ copilot.getType(p.type).subpages !== false || copilot.getType(p.type).files !== false }">

                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/copilot/pages'){p.relpath}" if="{ copilot.getType(p.type).subpages !== false }">@lang('Sub Pages')</a></li>
                    <li><a href="@route('/copilot/files'){p.relpath}" if="{ copilot.getType(p.type).files !== false }">@lang('Files')</a></li>
                </ul>

                <div class="uk-margin" if="{ copilot.getType(p.type).subpages !== false }">
                    <strong class="uk-text-small">@lang('Sub Pages')</strong>
                    <cp-pagejumplist dir="{p.dir}"></cp-pagejumplist>
                </div>

            </div>
        </li>
        <li data-uk-dropdown>
            <a href="@route('/copilot/page'.$page->relpath())"><i class="uk-icon-home" if="{page.isRoot}"></i> { page.meta.title.substring(0, 15) }</a>
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/copilot/files'){page.relpath}">@lang('Files')</a></li>
                </ul>
            </div>
        </li>
        <li><span class="uk-text-primary">@lang('Pages') <span class="uk-badge" show="{children.length}">{children.length}</span></span></li>
    </ul>

    <div class="uk-margin" if="{children.length}">
        <a class="uk-button uk-button-primary" onclick="{ createPage }" if="{ type.subpages !== false }">@lang('Create Page')</a>

        <div class="uk-form-icon uk-form uk-text-muted uk-float-right">

            <i class="uk-icon-filter"></i>
            <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter pages...')" onkeyup="{ update }">

        </div>
    </div>

    <div name="container" class="uk-grid uk-grid-match uk-grid-width-medium-1-3 uk-grid-width-large-1-4 uk-sortable" show="{children.length}">

        <div class="uk-grid-margin" each="{child,idx in children}" show="{ infilter(child) }" data-path="{ child.path }">
            <div class="uk-panel uk-panel-box uk-panel-card">
                <div class="uk-flex uk-flex-middle">
                    
                    <a class="uk-flex-item-1 uk-text-small uk-text-truncate" href="@route('/copilot/page'){ child.relpath }">{ copilot.getTypeLabel(child.type) }</a>
                    
                    <span class="uk-margin-small-right" data-uk-dropdown="pos:'bottom-right'">
                        <i class="uk-icon-cog uk-text-{ child.visible ? 'success':'danger' }"></i>
                        <div class="uk-dropdown uk-dropdown-close">

                            <div class="uk-margin" if="{ copilot.getType(child.type).subpages !== false }">
                                <strong class="uk-text-small">@lang('Sub Pages')</strong>
                                <cp-pagejumplist class="uk-text-small" dir="{child.dir}"></cp-pagejumplist>
                            </div>

                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Browse')</li>
                                <li><a href="@route('/copilot/pages'){child.relpath}">@lang('Sub Pages')</a></li>
                                <li><a href="@route('/copilot/files'){child.relpath}">@lang('Files')</a></li>
                                <li class="uk-nav-divider"></li>
                                <li><a onclick="{ parent.remove }" data-path="{ child.path }">@lang('Delete')</a></li>
                            </ul>
                        </div>
                    </span>

                </div>
                <div class="uk-position-relative">
                    <canvas width="600" height="400"></canvas>
                    <div class="uk-position-cover uk-flex uk-flex-middle uk-flex-center"><strong class="uk-text-muted">{ child.meta.title }</strong></div>
                    <a class="uk-position-cover" href="@route('/copilot/page'){ child.relpath }"></a>
                </div>
            </div>
        </div>

    </div>

    <div class="uk-margin-large-top uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center uk-animation-scale" if="{!children.length}">

        <div class="">

            <h1 class="uk-text-bold">@lang('Sub Pages')</h1>

            <p class="uk-h2 uk-text-muted">
                { App.i18n.get('This page has no sub-pages yet') }
            </p>
            <p class="uk-margin-large-top" if="{ type.subpages !== false }">
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

                App.request('/copilot/utils/updatePagesOrder', {order: order}).then(function(){
                    App.ui.notify("Pages reordered", "success");
                });
            });
        });

        createPage(e) {

            var options = {};

            options.parentType = this.page.type;

            if (this.type && this.type.subpages) {

                var subpages = Array.isArray(this.type.subpages) ? this.type.subpages : [this.type.subpages];

                options.types  = {};

                subpages.forEach(function(type) {
                    if (COPILOT_PAGE_TYPES[type]) {
                        options.types[type] = COPILOT_PAGE_TYPES[type];
                    }
                });
            }

            copilot.createPage(this.page.isRoot ? '/':this.page.contentdir, options);
        }

        remove(e) {
            
            e.preventDefault();

            var path = e.item.child.path;

            App.ui.confirm("Are you sure?", function() {

                App.request('/copilot/utils/deletePage', {path:path}).then(function(data) {

                    App.ui.notify("Page removed", "success");

                    $this.children.splice(e.item.idx, 1);
                    $this.update();
                });
            });
        }

        infilter(page, value, name) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = page.meta.title.toLowerCase();

            return name.indexOf(value) !== -1;
        }

    </script>

</div>
