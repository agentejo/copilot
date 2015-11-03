<ul  class="uk-breadcrumb">
    @render('cocopi:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Pages')</span></li>
</ul>


<div riot-view>

    <div class="uk-grid">
        <div class="uk-grid-margin uk-width-medium-3-4">

            <div class="uk-margin">

                <div class="uk-panel uk-panel-space uk-panel-box uk-text-center" if="{!home}">
                    <i class="uk-icon-home uk-text-large"></i>
                    <p>
                        @lang('No homepage.') <a onclick="{ createPage }" root="home">@lang('Create one.')</a>
                    </p>
                </div>

                <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{home}">
                    <div class="uk-grid">
                        <div class="">
                            <span class="uk-margin-small-right" data-uk-dropdown>
                                <i class="uk-icon-home uk-text-{ home.visible ? 'success':'danger' }"></i>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">@lang('Browse')</li>
                                        <li><a href="@route('/cocopi/pages'){home.relpath}">@lang('Sub Pages')</a></li>
                                        <li><a href="@route('/cocopi/files'){home.relpath}">@lang('Files')</a></li>
                                        <li class="uk-nav-divider"></li>
                                        <li><a onclick="{ remove }" data-path="{ home.path }">@lang('Delete')</a></li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                        <div class="uk-flex-item-1">
                            <a href="@route('/cocopi/page'){ home.relpath }">{ home.meta.title || home.basename }</a>
                        </div>
                        <div class="uk-text-muted uk-text-small">
                            { cocopi.getTypeLabel(home.type) }
                        </div>
                        <div class="uk-width-1-5 uk-text-muted uk-text-small uk-text-tuncate">
                            <a href="{ home.url }" target="_blank">/</a>
                        </div>
                        <div>
                            <span class="uk-badge">{ home.children }</span>
                        </div>
                    </div>

                </div>
            </div>

            <div name="container" class="uk-margin uk-sortable" show="{pages.length}">

                <div class="uk-panel uk-panel-box uk-panel-card uk-margin" each="{ page,idx in pages }" if="{ !page.isRoot }"  data-path="{ page.path }">

                    <div class="uk-grid">
                        <div>
                            <span class="uk-margin-small-right" data-uk-dropdown>
                                <i class="uk-icon-file-text-o uk-text-{ page.visible ? 'success':'danger' }"></i>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">@lang('Browse')</li>
                                        <li><a href="@route('/cocopi/pages'){page.relpath}">@lang('Sub Pages')</a></li>
                                        <li><a href="@route('/cocopi/files'){page.relpath}">@lang('Files')</a></li>
                                        <li class="uk-nav-divider"></li>
                                        <li><a onclick="{ parent.remove }" data-path="{ page.path }">@lang('Delete')</a></li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                        <div class="uk-flex-item-1">
                            <a href="@route('/cocopi/page'){ page.relpath }">{ page.meta.title || page.basename }</a>
                        </div>
                        <div class="uk-text-muted uk-text-small">
                            { cocopi.getTypeLabel(page.type) }
                        </div>
                        <div class="uk-width-1-5 uk-text-muted uk-text-small uk-text-tuncate">
                            <a href="{ page.url }" target="_blank">/{ page.slug }</a>
                        </div>
                        <div>
                            <span class="uk-badge">{ page.children }</span>
                        </div>
                    </div>

                </div>

                <a class="uk-button uk-button-large uk-width-1-1 uk-width-medium-1-4 uk-button-primary" onclick="{ createPage }" title="@lang('Create another page')" data-uk-tooltip="\{pos:'right'\}" root="/"><i class="uk-icon-plus"></i></a>

            </div>

            <div class="uk-margin uk-panel uk-panel-box uk-text-center" if="{!pages.length}">

                <i class="uk-icon-file-text-o uk-text-large"></i>
                <p>
                    @lang('No pages.') <a onclick="{ createPage }" root="/">@lang('Create a page')</a>.
                </p>
            </div>

        </div>
        <div class="uk-grid-margin uk-width-medium-1-4">
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

                App.request('/cocopi/utils/updatePagesOrder', {order: order}).then(function(){
                    App.ui.notify("Pages reordered", "success");
                });
            });
        });

        createPage(e) {

            cocopi.createPage(e.target.getAttribute('root'));
        }

        remove(e) {

            var path = e.target.getAttribute('data-path'),
                isHome = !e.item;

            App.ui.confirm("Are you sure?", function() {

                App.request('/cocopi/utils/deletePage', {path:path}).then(function(data) {

                    if (isHome) {
                        $this.home = null;
                    } else {
                        $this.pages.splice(e.item.idx, 1);
                    }

                    App.ui.notify("Page removed", "success");

                    $this.update();
                });
            });
        }

    </script>

</div>
