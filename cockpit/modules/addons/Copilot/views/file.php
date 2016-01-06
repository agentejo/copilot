<div class="uk-form" riot-view>

    <ul  class="uk-breadcrumb">
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
                    <strong class="uk-text-small">Sub pages</strong>
                    <cp-pagejumplist dir="{p.dir}"></cp-pagejumplist>
                </div>
            </div>
        </li>
        <li><a href="@route('/copilot/files'.$file->parent()->relpath())">@lang('Files')</a></li>
        <li><span class="uk-text-primary">{ file.filename }</span></li>
    </ul>

    <div class="uk-grid">
        <div class="uk-grid-margin uk-width-medium-2-3">
            <div class="uk-viewport-height-1-2 uk-flex uk-flex-center uk-flex-middle uk-panel uk-panel-box uk-panel-card { file.isImage ? 'bg-image':''}">
                <div class="uk-width-1-1">
                    <div if="{file.isImage}">
                        <img riot-src="{ file.url }" />
                    </div>
                    <div class="uk-text-xlarge uk-text-center" if="{!file.isImage}">
                        <i class="uk-icon-{ copilot.getFileIconCls(file.filename) }"></i>
                        <p>
                            { file.ext.toUpperCase() }
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="uk-grid-margin uk-width-medium-1-3">
            <form class="uk-form" onsubmit="{ save }">

                <div class="uk-form-row">
                    <label class="uk-text-small">@lang('Filename')</label>
                    <div>
                        <input field="text" class="uk-form-large uk-width-1-1" bind="file.filename" required>
                    </div>
                </div>

                <div class="uk-form-row">

                    <div class="uk-grid uk-grid-small uk-text-small uk-text-muted">
                        <div class="uk-grid-margin uk-width-medium-1-3">@lang('Size')</div>
                        <div class="uk-grid-margin uk-width-medium-2-3">{ file.fsize }</div>
                        <div class="uk-grid-margin uk-width-medium-1-3" if="{file.isImage}">@lang('Dimension')</div>
                        <div class="uk-grid-margin uk-width-medium-2-3" if="{file.isImage}">{ file.imageSize.width } x { file.imageSize.height }</div>
                        <div class="uk-grid-margin uk-width-medium-1-3">@lang('Mime')</div>
                        <div class="uk-grid-margin uk-width-medium-2-3">{ file.mime }</div>
                        <div class="uk-grid-margin uk-width-medium-1-3">@lang('Public link')</div>
                        <div class="uk-grid-margin uk-width-medium-2-3 uk-text-truncate"><a href="{file.url}" target="_blank">{ file.url }</a></div>
                    </div>

                </div>

                <div class="uk-form-row">

                    <div>@lang('Meta')</div>

                    <div class="uk-margin" if="{file.isImage}">
                        <label class="uk-text-small">@lang('Alternate Text')</label>
                        <input type="text" class="uk-width-1-1" bind="file.meta.alt">
                    </div>

                    <div class="uk-margin" each="{name, field in meta}" if="{ (!field.filter || App.Utils.fnmatch(field.filter, parent.file.filename)) }" no-reorder>

                        <label class="uk-text-small">
                            { field.label || name }
                        </label>

                        <div class="uk-margin uk-text-small uk-text-muted" show="{ field.info }">
                            { field.info || ' ' }
                        </div>

                        <div class="uk-margin">
                            <cp-field field="{ field }" bind="file.meta.{name}" cls="uk-form-large"></cp-field>
                        </div>

                    </div>

                </div>

                <div class="uk-form-row">
                    <button class="uk-button uk-button-large uk-button-primary uk-width-1-1">@lang('Save')</button>
                </div>

            </form>
        </div>
    </div>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.file     = {{ json_encode($file->toArray()) }};
        this.parents  = {{ json_encode(array_reverse($file->parents()->toArray())) }};
        this.pagetype = {{ json_encode($pagetype) }};

        this.meta     = App.$.extend(true, {
            desc: {label:'Description', type:'textarea'},
            tags: {label:'Tags', type: 'text'}
        }, (this.pagetype.files && this.pagetype.files.meta) || {});

        save() {

            App.request('/copilot/utils/updateFile', {file:this.file}).then(function(file) {
                App.ui.notify("File updated", "success");
            });
        }

    </script>

</div>

<style media="screen">
    .bg-image {
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyFpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIE1hY2ludG9zaCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpCOTdEOEI3OUE3MDMxMUUzOEIxNEZERTM0N0EzRjlGMSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpCOTdEOEI3QUE3MDMxMUUzOEIxNEZERTM0N0EzRjlGMSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkI5N0Q4Qjc3QTcwMzExRTM4QjE0RkRFMzQ3QTNGOUYxIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkI5N0Q4Qjc4QTcwMzExRTM4QjE0RkRFMzQ3QTNGOUYxIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+jGcG/wAAAAlQTFRF////zMzMzc3NCvMx6wAAACJJREFUeNpiYGRkYgQBBhgYIAGEBCO6SpIFmKCADDMAAgwATVgAkU8MrdIAAAAASUVORK5CYII=);
    }
</style>
