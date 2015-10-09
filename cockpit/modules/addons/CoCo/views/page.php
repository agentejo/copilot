<div class="uk-form" riot-view>

    <ul  class="uk-breadcrumb">
        @render('coco:views/partials/subnav.php')
        <li each="{p in parents}" data-uk-dropdown>
            <a href="@route('/coco/page'){ p.relpath }">
                <i class="uk-icon-home" if="{p.isRoot}"></i> { p.meta.title.substring(0, 15) }
            </a>
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/coco/pages'){p.relpath}">@lang('Sub Pages')</a></li>
                    <li><a href="@route('/coco/files'){p.relpath}">@lang('Files')</a></li>
                </ul>
            </div>
        </li>
        <li><a class="uk-text-primary" href="{ page.url }" target="_blank"><i class="uk-icon-home" if="{page.isRoot}"></i> { page.meta.title }</a></li>
    </ul>

    <form onsubmit="{ save }">
        <div class="uk-grid uk-grid-divider">
            <div class="uk-width-medium-2-3">

                <h3>@lang('Page')</h3>

                <div class="uk-form-row">
                    <label class="uk-text-small">@lang('Title')</label>
                    <div>
                        <input type="text" bind="page.rawmeta.title" class="uk-form-large uk-width-1-1" required>
                    </div>
                </div>

                <div class="uk-form-row" if="{!page.isRoot}">
                    <label class="uk-text-small">@lang('Slug')</label>
                    <div>
                        <input type="text" bind="updates.slug" placeholder="{ page.slug }" class="uk-form-large uk-width-1-1">
                    </div>
                </div>


                <ul class="uk-tab uk-margin-large-top">
                    <li class="{ view == 'fields' ? 'uk-active':'' }" if="{ App.Utils.count(type.meta) }"><a onclick="{ toggleView }">@lang('Fields')</a></li>
                    <li class="{ view == 'content' ? 'uk-active':'' }" if="{ App.Utils.count(type.meta) }"><a onclick="{ toggleView }">@lang('Content')</a></li>
                    <li class="{ view == 'content' ? 'uk-active':'' }" if="{ !App.Utils.count(type.meta) }"><a>@lang('Content')</a></li>
                </ul>

                <div class="uk-margin-large-top" show="{view == 'fields'}">

                    <div class="uk-margin" each="{name, field in type.meta}">

                        <div class="uk-panel">

                            <label>
                                <i class="uk-icon-ellipsis-v"></i>
                                { field.label || name }
                            </label>

                            <div class="uk-margin uk-text-small uk-text-muted">
                                { field.info || ' ' }
                            </div>

                            <div class="uk-margin">
                                <cp-field field="{ field }" bind="page.rawmeta.{name}" cls="uk-form-large"></cp-field>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="uk-margin-large-top" show="{view == 'content'}">

                    <cp-field field="{ page.ext == 'html' ? 'html':'markdown'}" bind="page.rawcontent" cls="uk-form-large"></cp-field>

                </div>

            </div>
            <div class="uk-width-medium-1-3">
                <h3>@lang('Settings')</h3>

                <div class="uk-panel">

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('Visibility')</label>
                        <div>
                            <field-boolean bind="page.visible" cls="uk-form-large uk-width-1-1 uk-margin-small-top"></field-boolean>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('Description')</label>
                        <div>
                            <textarea bind="page.rawmeta.description" placeholder="{page.meta.description}" class="uk-form-large uk-width-1-1"></textarea>
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('Keywords')</label>
                        <div>
                            <input field="text" bind="page.rawmeta.keywords" placeholder="{page.meta.keywords}" class="uk-form-large uk-width-1-1">
                        </div>
                    </div>

                    <div class="uk-form-row">
                        <label class="uk-text-small">@lang('Author')</label>
                        <div>
                            <input field="text" bind="page.rawmeta.author" placeholder="{page.meta.author}" class="uk-form-large uk-width-1-1">
                        </div>
                    </div>

                </div>

                <div class="uk-panel uk-panel-box">

                    <h5>@lang('Sub Pages')</h5>

                    <div class="{ children.length > 5 ? 'uk-scrollable-box':'' }" if="{children.length}">
                        <ul class="uk-list uk-list-space" if="{children.length}">
                            <li each="{child in children}">

                                <div class="uk-grid uk-grid-small">
                                    <div>
                                        <span class="uk-margin-small-right" data-uk-dropdown>
                                            <i class="uk-icon-file-text-o uk-text-{ child.visible ? 'success':'danger' }"></i>
                                            <div class="uk-dropdown">
                                                <ul class="uk-nav uk-nav-dropdown">
                                                    <li class="uk-nav-header">@lang('Browse')</li>
                                                    <li><a href="@route('/coco/pages'){child.relpath}">@lang('Sub Pages')</a></li>
                                                    <li><a href="@route('/coco/files'){child.relpath}">@lang('Files')</a></li>
                                                </ul>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="uk-flex-item-1 uk-text-truncate">
                                        <a href="@route('/coco/page'){ child.relpath }">{ child.meta.title }</a>
                                    </div>
                                    <div>
                                        <span class="uk-badge">{ child.children }</span>
                                    </div>
                                </div>

                            </li>
                        </ul>
                    </div>

                    <div class="uk-text-muted" if="{!children.length}">
                        @lang('This page has no sub-pages'). <a onclick="{ createPage }">@lang('Create one')</a>.
                    </div>

                    <div class="uk-margin uk-button-group">
                        <a href="@route('/coco/pages'.$page->relpath())" class="uk-button uk-button-mini">@lang('Browse')</a>
                        <a class="uk-button uk-button-mini uk-button-primary" onclick="{ createPage }" if="{children.length}">@lang('Add Page')</a>
                    </div>

                </div>

                <div class="uk-panel uk-panel-box">

                    <h5>@lang('Files')</h5>

                    <div class="{ files.length > 5 ? 'uk-scrollable-box':'' }" if="{files.length}">
                        <ul class="uk-list uk-list-space" if="{files.length}">
                            <li each="{file in files}">

                                <div class="uk-grid uk-grid-small">
                                    <div>
                                        <i class="uk-icon-{ coco.getFileIconCls(file.filename) }"></i>
                                    </div>
                                    <div class="uk-flex-item-1 uk-text-truncate">
                                        <a href="@route('/coco/file'){ file.relpath }">{ file.filename }</a>
                                    </div>
                                    <div>
                                        <span class="uk-text-small uk-text-muted">{ file.fsize }</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="uk-text-muted" if="{!files.length}">
                        @lang('This page has no files')
                    </div>

                    <div class="uk-margin">
                        <a href="@route('/coco/files'.$page->relpath())" class="uk-button uk-button-mini">@lang('Browse')</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-margin">
            <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
        </div>
    </form>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.page      = {{ json_encode($page->toArray()) }};
        this.parents   = {{ json_encode(array_reverse($page->parents()->toArray())) }};
        this.children  = {{ json_encode($page->children()->toArray()) }};
        this.files     = {{ json_encode($page->files()->toArray()) }};
        this.type      = {{ json_encode($type) }};

        this.updates   = {
            slug: ''
        };

        this.view = App.Utils.count(this.type.meta) ? 'fields':'content';

        toggleView() {
            this.view = this.view == 'fields' ? 'content':'fields';

            setTimeout(function(){
                UIkit.Utils.checkDisplay();
            }, 50);
        }

        createPage(e) {
            coco.createPage(this.page.isRoot ? '/':this.page.contentdir);
        }

        save() {

            App.request('/coco/utils/updatePage', {page: this.page, updates: this.updates}, 'text').then(function(res) {

                App.ui.notify("Page updated", "success");

                setTimeout(function(){
                    location.href= App.route('/coco/page'+res);
                }, 1000);
            });
        }

    </script>

</div>
