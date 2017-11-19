<div class="uk-form" riot-view>

    <ul  class="uk-breadcrumb">
        @render('copilot:views/partials/subnav.php')
        <li each="{p in parents}" data-uk-dropdown>
            <a href="@route('/copilot/page'){ p.relpath }">
                <i class="uk-icon-home" if="{p.isRoot}"></i> { p.meta.title.substring(0, 15) }
            </a>
            <div class="uk-dropdown" if="{ copilot.getType(p.type).subpages !== false || copilot.getType(p.type).files !== false }">

                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/copilot/pages'){p.relpath}" if="{ copilot.getType(p.type).subpages !== false }">@lang('Sub Pages')</a></li>
                    <li><a href="@route('/copilot/files'){p.relpath}" if="{ copilot.getType(p.type).files !== false }">@lang('Files')</a></li>
                </ul>

                <div class="uk-margin" if="{ copilot.getType(p.type).subpages !== false }">
                    <strong class="uk-text-small">Sub pages</strong>
                    <cp-pagejumplist class="uk-text-small" dir="{p.dir}"></cp-pagejumplist>
                </div>

            </div>
        </li>
        <li data-uk-dropdown>
            <a class="{ page.visible ? 'uk-text-primary':'uk-text-danger'}" onclick="{ showPreview }">
                <i class="uk-icon-home" if="{page.isRoot}"></i>
                <i class="uk-icon-eye{ page.visible ? '':'-slash'}" if="{!page.isRoot}"></i>
                { page.meta.title }
            </a>
            <div class="uk-dropdown" if="{ copilot.getType(page.type).subpages !== false || copilot.getType(page.type).files !== false }">

                <ul class="uk-nav uk-nav-dropdown uk-dopdown-close">
                    <li class="uk-nav-header">@lang('Browse')</li>
                    <li><a href="@route('/copilot/pages'){page.relpath}" if="{ copilot.getType(page.type).subpages !== false }">@lang('Sub Pages')</a></li>
                    <li><a href="@route('/copilot/files'){page.relpath}" if="{ copilot.getType(page.type).files !== false }">@lang('Files')</a></li>
                    <li class="uk-nav-divider"></li>
                    <li><a onclick="{ showPreview }">@lang('View Page')</a></li>
                </ul>

            </div>
        </li>
    </ul>

    <div class="uk-alert uk-alert-danger" if="{ !page.isWritable }">
        <p>@lang('This page is not writable')</p>
    </div>

    <form onsubmit="{ save }">

        <div class="uk-grid">

            <div class="uk-grid-margin uk-width-medium-3-4">

                <div class="uk-form-row">
                    <span class="uk-badge">{ copilot.getTypeLabel(page.type) }</span>
                </div>

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

                <ul class="uk-tab uk-margin" if="{ tabs && tabs.length > 1 }">
                    <li class="{ t == parent.tab ? 'uk-active':'' }" each="{t in tabs}">
                        <a onclick="{ parent.selectTab }" select="{t}">{ App.i18n.get(t) }</a>
                    </li>
                </ul>

                <div class="uk-grid" show="{tab == name}" tab="{name}" each="{group, name in meta}">

                    <div class="uk-width-medium-{field.width || '1-1'} uk-grid-margin" each="{field, fname in group}" no-reorder>

                        <label class="uk-text-bold">
                            { field.label || fname }
                        </label>

                        <div class="uk-margin uk-text-small uk-text-muted">
                            { field.info || ' ' }
                        </div>

                        <div class="uk-margin">
                            <cp-field type="{field.type || 'text'}" bind="page.rawmeta.{fname}" opts="{ field.options || {} }"></cp-field>
                        </div>

                    </div>
                </div>

                <div class="uk-grid-margin" show="{tab == 'Content'}" tab="Content" if="{ type.content.visible!==false }">

                    <cp-field type="{contentType}" bind="page.rawcontent"></cp-field>

                </div>

                <div class="uk-grid-margin" if="{ type.subpages && !type.content.visible && !Object.keys(meta).length }">
                    <!-- implementation needed -->
                </div>

                <div class="uk-margin">
                    <button class="uk-button uk-button-large uk-button-primary uk-width-1-1 uk-width-medium-1-4">@lang('Save')</button>
                </div>

            </div>
            <div class="uk-grid-margin uk-width-medium-1-4">
                <div class="uk-panel-box uk-panel-card">

                    <h3>@lang('Settings')</h3>

                    <div class="uk-panel">

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Visibility')</label>
                            <div class="uk-margin-small-top">
                                <field-boolean bind="page.visible"></field-boolean>
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Description')</label>
                            <div>
                                <textarea bind="page.rawmeta.description" placeholder="{page.meta.description}" class="uk-form-large uk-text-muted uk-width-1-1"></textarea>
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Keywords')</label>
                            <div>
                                <input field="text" bind="page.rawmeta.keywords" placeholder="{page.meta.keywords}" class="uk-form-large uk-text-muted uk-width-1-1">
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">@lang('Author')</label>
                            <div>
                                <input field="text" bind="page.rawmeta.author" placeholder="{page.meta.author}" class="uk-form-large uk-text-muted uk-width-1-1">
                            </div>
                        </div>

                    </div>

                    <div class="uk-margin-top" if="{type.subpages !== false }">

                        <h5 class="uk-clearfix"><i class="uk-icon-sitemap uk-margin-small-right"></i> @lang('Sub Pages') <span class="uk-text-muted uk-text-small uk-float-right" if="{children.length > 5}">{ children.length }</span></h5>

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
                                                        <li><a href="@route('/copilot/pages'){child.relpath}">@lang('Sub Pages')</a></li>
                                                        <li><a href="@route('/copilot/files'){child.relpath}">@lang('Files')</a></li>
                                                    </ul>
                                                </div>
                                            </span>
                                        </div>
                                        <div class="uk-flex-item-1 uk-text-truncate">
                                            <a href="@route('/copilot/page'){ child.relpath }">{ child.meta.title }</a>
                                        </div>
                                        <div>
                                            <span class="uk-badge">{ child.children }</span>
                                        </div>
                                    </div>

                                </li>
                            </ul>
                        </div>

                        <div class="uk-text-muted" if="{!children.length}">
                            @lang('This page has no sub-pages').
                        </div>

                        <div class="uk-margin">
                            <a href="@route('/copilot/pages'.$page->relpath())" class="uk-text-small uk-margin-small-right">@lang('Browse Pages')</a>
                            <a class="uk-text-small" onclick="{ createPage }" title="@lang('Add Page')" data-uk-tooltip><i class="uk-icon-plus-circle"></i></a>
                        </div>
                    </div>

                    <div class="uk-margin-top" if="{type.files !== false }">

                        <h5 class="uk-clearfix"><i class="uk-icon-folder-o uk-margin-small-right"></i> @lang('Files') <span class="uk-text-muted uk-text-small uk-float-right" if="{files.length > 5}">{ files.length }</span></h5>

                        <div class="{ files.length > 5 ? 'uk-scrollable-box':'' }" if="{files.length}">
                            <ul class="uk-list uk-list-space" if="{files.length}">
                                <li each="{file in files}">

                                    <div class="uk-grid uk-grid-small">
                                        <div>
                                            <i class="uk-icon-{ copilot.getFileIconCls(file.filename) }"></i>
                                        </div>
                                        <div class="uk-flex-item-1 uk-text-truncate">
                                            <a href="@route('/copilot/file'){ file.relpath }">{ file.filename }</a>
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
                            <a href="@route('/copilot/files'.$page->relpath())" class="uk-text-small">@lang('Browse Files')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <div id="modal-preview" class="uk-modal">
        <div class="uk-modal-dialog uk-modal-dialog-blank uk-height-viewport uk-flex uk-flex-column">
            <div class="uk-flex uk-flex-middle preview-header">

                <span class="uk-margin-left uk-text-bold uk-text-primary"><i class="uk-icon-eye uk-margin-small-right"></i> @lang('Live')</span>

                <ul class="uk-subnav uk-text-large uk-margin-left uk-flex-item-1 uk-flex-center uk-margin-small-top">
                    <li><a onclick="App.$('#preview-frame').css('max-width', 360).attr('screen', 'mobile')"><i class="uk-icon-mobile-phone"></i></a></li>
                    <li><a onclick="App.$('#preview-frame').css('max-width', 768).attr('screen', 'tablet')"><i class="uk-icon-tablet"></i></a></li>
                    <li><a onclick="App.$('#preview-frame').css('max-width', '100%').attr('screen', '')"><i class="uk-icon-desktop"></i></a></li>
                </ul>

                <a class="uk-margin-right" href="{{ $page->url() }}" target="_blank"><i class="uk-icon-share"></i></a>

                <a class="uk-modal-close uk-link-muted uk-margin-right"><i class="uk-icon-button uk-icon-close"></i></a>

            </div>
            <div class="uk-position-relative uk-bg-light uk-flex-item-1">
                <iframe id="preview-frame" name="preview-frame" class="uk-position-top uk-container-center" riot-src="{ page.url }?_m={page.meta.modified}" width="100%" height="100%"></iframe>
            </div>
        </div>
    </div>

    <style>

        .preview-header {
            box-shadow:0 0 20px rgba(0,0,0,0.07);
            z-index:1;
        }

        #preview-frame {
            transition: max-width 300ms, margin-top 300ms, box-shadow 300ms;
            max-width: 100%;
            margin-top: 0px;
            box-shadow: 0 0 20px rgba(0,0,0,0);
        }

        #preview-frame[screen='mobile'],
        #preview-frame[screen='tablet'] {
            box-shadow: 0 0 40px rgba(0,0,0,.2);
            margin-top: 30px;
            height: calc(100% - 60px);
        }

    </style>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.page      = {{ json_encode($page->toArray()) }};
        this.parents   = {{ json_encode(array_reverse($page->parents()->toArray())) }};
        this.children  = {{ json_encode($page->children()->sorted()->toArray()) }};
        this.files     = {{ json_encode($page->files()->sorted()->toArray()) }};
        this.type      = {{ json_encode($type) }};
        this.updates   = { slug: '' };
        this.meta      = {};

        this.contentType = this.type.content.type ?  this.type.content.type : this.type.ext == 'md' ? 'markdown':'html';
        this.view = App.Utils.count(this.type.meta) ? 'fields':'content';

        this.page.rawmeta = this.page.rawmeta || {};

        Object.keys(this.type.meta || {}).forEach(function(key, group){

            group = $this.type.meta[key].group || 'Main';

            if (!$this.meta[group]) {
                $this.meta[group] = {};
            }

            $this.meta[group][key] = $this.type.meta[key];

            // fill with default values
            if ($this.page.rawmeta[key] === undefined) {
                $this.page.rawmeta[key] = $this.type.meta[key].options && $this.type.meta[key].options.default || null;
            }
        });

        this.on('mount', function() {

            App.session.set('app.finder.path', App.Utils.dirname(this.page.relpath));

            this.tabs = [];

            App.$(this.root).find('[tab]').each(function() {
                $this.tabs.push(this.getAttribute('tab'));
            });

            this.tab = this.tabs[0] || '';

            // handle uploads
            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {
                    action: App.route('/media/api'),
                    params: {"cmd":"upload"},
                    type: 'json',
                    before: function(options) {
                        options.params.path = App.Utils.dirname($this.page.relpath);
                    },
                    loadstart: function() {
                        this.doctitle = document.title;
                    },
                    progress: function(percent) {
                        document.title = 'Upload:'+Math.ceil(percent)+'%';
                    },
                    allcomplete: function(response) {

                        document.title = this.doctitle;

                        if (response && response.failed && response.failed.length) {
                            App.ui.notify("File(s) failed to uploaded.", "danger");
                        }

                        if (response && response.uploaded && response.uploaded.length) {
                            App.ui.notify("File(s) uploaded.", "success");
                        }

                        if (!response) {
                            App.ui.notify("Something went wrong.", "danger");
                        }

                        App.request('/copilot/utils/getPageResources', {path:$this.page.path}).then(function(data) {

                            setTimeout(function(){
                                $this.files = data || [];
                                $this.update();
                            }, 100);
                        });
                    }
                };

                UIkit.uploadDrop('body', uploadSettings);
                UIkit.init(this.root);

                $this.update();
            });

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
                $this.save(e);
                return false;
            });

            App.$(this.root).on('submit', function(e, component) {
                if (component) $this.save(e);
            });
        });

        selectTab(e) {

            this.tab = e.target.getAttribute('select');

            setTimeout(function(){
                UIkit.Utils.checkDisplay();
            }, 50);
        }

        createPage(e) {

            var options = {};

            if (this.type && this.type.subpages) {

                var subpages = Array.isArray(this.type.subpages) ? this.type.subpages : [this.type.subpages];

                options.types = {};
                options.parentType = this.page.type;

                subpages.forEach(function(type) {
                    if (COPILOT_PAGE_TYPES[type]) {
                        options.types[type] = COPILOT_PAGE_TYPES[type];
                    }
                });
            }

            copilot.createPage(this.page.isRoot ? '/':this.page.contentdir, options);
        }

        generateSlugFromTitle() {

            if (this.page.rawmeta.title) {
                this.updates.slug = App.Utils.sluggify(this.page.rawmeta.title.toLowerCase());
            }
        }

        showPreview() {
            App.$('#preview-frame').attr('src', [this.page.url, '?_m=', this.page.meta.modified, '&nc=', Math.random()].join(''));
            UIkit.modal('#modal-preview').show();
        }

        save(e) {

            if(e) e.preventDefault();

            if (!$this.page.isWritable) {
                App.ui.alert("This page is not writable!");
                return false;
            }

            App.request('/copilot/utils/updatePage', {page: this.page, updates: this.updates}).then(function(page) {

                App.$.extend($this.page, page);
                $this.updates = { slug: '' };
                $this.update();

                App.ui.notify("Page updated", "success");
            });
        }

    </script>

</div>
