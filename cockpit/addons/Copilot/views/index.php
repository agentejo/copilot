<ul class="uk-breadcrumb">
    @render('copilot:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Pages')</span></li>
</ul>

@if($app->module("copilot")->getLicense()->type == 'trial')
<div class="uk-flex uk-text-muted uk-margin-bottom">
    <div class="uk-margin-small-right">
        <img class="uk-svg-adjust" src="@base('copilot:assets/media/icons/license.svg')" width="50" alt="License" data-uk-svg>
    </div>
    <div class="uk-flex-item-1">
        <span class="uk-badge uk-badge-danger">@lang('Free Trial')</span>
        <div class="uk-margin-small-top">
            @lang('Unlicensed version. Enjoy your free trial.')
            <a href="http://agentejo.com" target="_blank">@lang('Buy a license')</a>
        </div>
    </div>
</div>
@end

<div riot-view>

    <h2 class="uk-margin">@lang('Pages')</h2>

    <div class="uk-margin-small-bottom">

        <div class="uk-panel uk-panel-space uk-panel-box uk-text-center" if="{!home}">
            <i class="uk-icon-home uk-text-large"></i>
            <p>
                @lang('No homepage.') <a onclick="{ createPage }" root="home">@lang('Create one.')</a>
            </p>
        </div>

        <div class="uk-panel uk-panel-box uk-panel-card uk-margin" if="{home}">
            <div class="uk-grid uk-flex-middle uk-grid-small">
                <div class="">
                    <span class="uk-margin-small-right" data-uk-dropdown>
                        <i class="uk-icon-home uk-text-{ home.visible ? 'success':'danger' }"></i>
                        <div class="uk-dropdown uk-dropdown-close">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Browse')</li>
                                <li><a href="@route('/copilot/pages'){home.relpath}">@lang('Sub Pages')</a></li>
                                <li><a href="@route('/copilot/files'){home.relpath}">@lang('Files')</a></li>
                                <li class="uk-nav-divider"></li>
                                <li class="uk-nav-item-danger"><a onclick="{ remove }" data-path="{ home.path }">@lang('Delete')</a></li>
                            </ul>
                        </div>
                    </span>
                </div>
                <div class="uk-flex-item-1">
                    <a href="@route('/copilot/page'){ home.relpath }">{ home.meta.title || home.basename }</a>
                </div>
                <div class="uk-text-muted uk-text-small">
                    { copilot.getTypeLabel(home.type) }
                </div>
                <div>
                    <a class="{ home.visible ? '':'uk-text-muted uk-disabled' }" href="{ home.url }" title="{ home.url }" target="_blank" data-uk-tooltip><i class="uk-icon-globe"></i></a>
                </div>
                <div>
                    <span class="uk-badge">{ home.children }</span>
                </div>
            </div>

        </div>
    </div>

    <div name="container" class="uk-margin uk-sortable" show="{pages.length}">

        <div class="uk-panel uk-panel-box uk-panel-card uk-margin-small-bottom" each="{ page,idx in pages }" if="{ !page.isRoot }"  data-path="{ page.path }">

            <div class="uk-grid uk-flex-middle uk-grid-small">
                <div>
                    <span class="uk-margin-small-right" data-uk-dropdown>
                        <i class="uk-icon-cog uk-text-{ page.visible ? 'success':'danger' }"></i>
                        <div class="uk-dropdown uk-dropdown-close">

                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">@lang('Browse')</li>
                                <li><a href="@route('/copilot/pages'){page.relpath}">@lang('Sub Pages')</a></li>
                                <li><a href="@route('/copilot/files'){page.relpath}">@lang('Files')</a></li>
                                <li class="uk-nav-divider"></li>
                                <li class="uk-nav-item-danger"><a onclick="{ parent.remove }" data-path="{ page.path }">@lang('Delete')</a></li>
                            </ul>

                            <div class="uk-margin"  if="{ copilot.getType(page.type).subpages !== false }">
                                <strong class="uk-text-small">Sub pages</strong>
                                <cp-pagejumplist class="uk-text-small" dir="{page.dir}"></cp-pagejumplist>
                            </div>

                        </div>
                    </span>
                </div>
                <div class="uk-flex-item-1">
                    <a href="@route('/copilot/page'){ page.relpath }">{ page.meta.title || page.basename }</a>
                </div>
                <div class="uk-text-muted uk-text-small">
                    { copilot.getTypeLabel(page.type) }
                </div>
                <div>
                    <a class="{ page.visible ? '':'uk-text-muted uk-disabled' }" href="{ page.url }" title="{ page.url }" target="_blank" data-uk-tooltip><i class="uk-icon-globe"></i></a>
                </div>
                <div>
                    <span class="uk-badge">
                        { page.children }
                    </span>
                </div>
            </div>

        </div>

        <div class="uk-margin uk-text-center">
            <a class="uk-text-xlarge" onclick="{ createPage }" title="@lang('Create another page')" data-uk-tooltip="\{pos:'bottom'\}" root="/"><i class="uk-icon-plus-circle"></i></a>
        </div>

    </div>

    <div class="uk-margin uk-panel uk-panel-box uk-text-center" if="{!pages.length}">

        <i class="uk-icon-file-text-o uk-text-large"></i>
        <p>
            @lang('No pages.') <a onclick="{ createPage }" root="/">@lang('Create a page')</a>.
        </p>
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

                App.request('/copilot/utils/updatePagesOrder', {order: order}).then(function(){
                    App.ui.notify("Pages reordered", "success");
                });
            });
        });

        createPage(e) {

            copilot.createPage(e.target.getAttribute('root'));
        }

        remove(e) {

            var path = e.target.getAttribute('data-path'),
                isHome = !e.item;

            App.ui.confirm("Are you sure?", function() {

                App.request('/copilot/utils/deletePage', {path:path}).then(function(data) {

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
