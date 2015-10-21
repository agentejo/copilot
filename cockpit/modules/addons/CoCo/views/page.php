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
        <li>
            <a class="{ page.visible ? 'uk-text-primary':'uk-text-danger'}" href="{ page.url }" target="_blank">
                <i class="uk-icon-home" if="{page.isRoot}"></i>
                <i class="uk-icon-eye{ page.visible ? '':'-slash'}" if="{!page.isRoot}"></i>
                { page.meta.title }
            </a>
        </li>
    </ul>

    <form onsubmit="{ save }">

        <div class="uk-grid uk-grid-divider">

            <div class="uk-grid-margin uk-width-medium-3-4">

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
                    <div class="uk-text-small uk-margin uk-text-truncate uk-clearfix">
                        <a class="uk-text-muted uk-float-left" href="{ page.url }" target="_blank">/<span each="{p in parents}" if="{!p.isRoot}">{ p.slug }/</span>{ updates.slug || page.slug }</a>
                        <a class="uk-float-right" onclick="{generateSlugFromTitle}" show="{page.rawmeta.title}">@lang('Generate from title')</a>
                    </div>
                </div>
                <br>

                <div class="uk-margin" if="{ type.description }">
                    <div class="uk-alert">{ type.description }</div>
                </div>

                <ul class="uk-tab uk-margin">
                    <li class="{ t == parent.tab ? 'uk-active':'' }" each="{t in tabs}">
                        <a onclick="{ parent.selectTab }" select="{t}">{t}</a>
                    </li>
                </ul>

                <div class="uk-margin" show="{tab == 'Fields'}" tab="Fields" if="{ App.Utils.count(type.meta) }">

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

                <div class="uk-margin" show="{tab == 'Content'}" tab="Content" if="{ type.content.visible!==false }">

                    <cp-field field="{ contentType }" bind="page.rawcontent" cls="uk-form-large"></cp-field>

                </div>

            </div>
            <div class="uk-grid-margin uk-width-medium-1-4">
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

                    <h5 class="uk-clearfix">@lang('Sub Pages') <span class="uk-text-muted uk-text-small uk-float-right" if="{children.length > 5}">{ children.length }</span></h5>

                    <div class="{ children.length > 5 ? 'uk-scrollable-box':'' }" if="{children.length}">
                        <ul class="uk-list uk-list-space" if="{children.length}">
                            <li each="{child in children}">

                                <div class="uk-grid uk-grid-small">
                                    <div>
                                        <span class="uk-margin-small-right" data-uk-dropdown="\{pos:'left-center'\}">
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

                    <h5 class="uk-clearfix">@lang('Files') <span class="uk-text-muted uk-text-small uk-float-right" if="{files.length > 5}">{ files.length }</span></h5>

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
            <button class="uk-button uk-button-large uk-button-primary uk-width-1-1 uk-width-medium-1-4">@lang('Save')</button>
        </div>
    </form>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.page      = {{ json_encode($page->toArray()) }};
        this.parents   = {{ json_encode(array_reverse($page->parents()->toArray())) }};
        this.children  = {{ json_encode($page->children()->sorted()->toArray()) }};
        this.files     = {{ json_encode($page->files()->sorted()->toArray()) }};
        this.type      = {{ json_encode($type) }};
        this.updates   = { slug: '' };

        this.contentType = this.type.content.type ?  this.type.content.type : this.type.ext == 'md' ? 'markdown':'html';
        this.view = App.Utils.count(this.type.meta) ? 'fields':'content';

        this.on('mount', function() {

            this.tabs = [];

            App.$(this.root).find('[tab]').each(function() {
                $this.tabs.push(this.getAttribute('tab'));
            });

            this.tab = this.tabs[0] || '';

            this.update();
        });

        selectTab(e) {

            this.tab = e.target.getAttribute('select');

            setTimeout(function(){
                UIkit.Utils.checkDisplay();
            }, 50);
        }

        createPage(e) {
            coco.createPage(this.page.isRoot ? '/':this.page.contentdir);
        }

        generateSlugFromTitle() {

            if (this.page.rawmeta.title) {
                this.updates.slug = this.page.rawmeta.title.toLowerCase().replace(/\s/g, '-');
            }
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
