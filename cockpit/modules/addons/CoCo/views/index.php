<ul  class="uk-breadcrumb">
    @render('coco:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Dashboard')</span></li>
</ul>


<div riot-view>

    <div class="uk-grid">
        <div class="uk-grid-margin uk-width-medium-2-3">

            <div class="uk-margin">

                <div class="uk-panel uk-panel-space uk-panel-box uk-text-center" if="{!home}">
                    <i class="uk-icon-home uk-text-large"></i>
                    <p>
                        @lang('No homepage.') <a onclick="{ createPage }" root="home">@lang('Create one.')</a>
                    </p>
                </div>

                <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{home}">
                    <div class="uk-grid uk-grid-small">
                        <div class="">
                            <span class="uk-margin-small-right" data-uk-dropdown>
                                <i class="uk-icon-home uk-text-{ home.visible ? 'success':'danger' }"></i>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">@lang('Browse')</li>
                                        <li><a href="@route('/coco/pages'){home.relpath}">@lang('Sub Pages')</a></li>
                                        <li><a href="@route('/coco/files'){home.relpath}">@lang('Files')</a></li>
                                        <li class="uk-nav-divider"></li>
                                        <li><a onclick="{ remove }" data-path="{ home.path }">@lang('Delete')</a></li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                        <div class="uk-flex-item-1">

                            <a href="@route('/coco/page'){ home.relpath }">{ home.meta.title || home.basename }</a>
                        </div>
                        <div class="uk-width-1-5 uk-text-muted uk-text-small">
                            { home.type }
                        </div>
                        <div>
                            <span class="uk-badge">{ home.children }</span>
                        </div>
                    </div>

                </div>
            </div>

            <div name="container" class="uk-margin uk-margin-left uk-sortable" show="{pages.length}">

                <div class="uk-panel uk-panel-box uk-panel-card uk-margin" each="{ page,idx in pages }" if="{ !page.isRoot }"  data-path="{ page.path }">

                    <div class="uk-grid uk-grid-small">
                        <div>
                            <span class="uk-margin-small-right" data-uk-dropdown>
                                <i class="uk-icon-file-text-o uk-text-{ page.visible ? 'success':'danger' }"></i>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">@lang('Browse')</li>
                                        <li><a href="@route('/coco/pages'){page.relpath}">@lang('Sub Pages')</a></li>
                                        <li><a href="@route('/coco/files'){page.relpath}">@lang('Files')</a></li>
                                        <li class="uk-nav-divider"></li>
                                        <li><a onclick="{ parent.remove }" data-path="{ page.path }">@lang('Delete')</a></li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                        <div class="uk-flex-item-1">
                            <a href="@route('/coco/page'){ page.relpath }">{ page.meta.title || page.basename }</a>
                        </div>
                        <div class="uk-width-1-5 uk-text-muted uk-text-small">
                            { page.type }
                        </div>
                        <div>
                            <span class="uk-badge">{ page.children }</span>
                        </div>
                    </div>

                </div>

                <a class="uk-button uk-button-large uk-button-primary" onclick="{ createPage }" root="/">@lang('Create another page')</a>

            </div>

            <div class="uk-margin uk-panel uk-panel-box uk-text-center" if="{!pages.length}">

                <i class="uk-icon-file-text-o uk-text-large"></i>
                <p>
                    @lang('No pages.') <a onclick="{ createPage }" root="/">@lang('Create a page')</a>.
                </p>
            </div>

        </div>
        <div class="uk-grid-margin uk-width-medium-1-3">
            <div class="uk-panel">
                <h4>@lang('Site')</h4>

                <div class="uk-text-truncate">
                    <a href="{{ $app->pathToUrl('site:') }}" target="_blank">
                        <i class="uk-icon-home"></i> {{ str_replace(['http://', 'https://'],'', $app->getSiteUrl()) }}
                    </a>
                </div>
            </div>
        </div>
    </div>


    <script type="view/script">

        var $this = this;

        this.home =  {{ json_encode($home ? $home->toArray() : null) }};
        this.pages = {{ $pages->sorted()->toJSON() }};


        this.on('mount', function() {

            var sortable = UIkit.sortable(App.$('[name="container"]'), {animation: true}).element.on("change.uk.sortable", function(e, sortable, ele) {

                var order = [];

                sortable.element.children().each(function(index){
                    order.push(this.getAttribute('data-path'));
                });

                App.request('/coco/utils/updatePagesOrder', {order: order}).then(function(){
                    App.ui.notify("Pages reordered", "success");
                });
            });
        });


        createPage(e) {

            coco.createPage(e.target.getAttribute('root'));
        }

        remove(e) {

            var path = e.target.getAttribute('data-path'),
                isHome = !e.item;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('coco', 'deletePage', [path]).then(function(data) {

                    if (isHome) {
                        $this.home = null;
                    } else {
                        $this.pages.splice(e.item.idx, 1);
                    }

                    $this.update();
                });
            });
        }

    </script>

</div>
