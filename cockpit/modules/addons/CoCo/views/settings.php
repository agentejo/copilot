<ul  class="uk-breadcrumb">
    @render('coco:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Settings')</span></li>
</ul>

<div class="uk-form" riot-view>

    <form onsubmit="{ save }">

        <div class="uk-grid">
            <div class="uk-width-2-3">

                <label class="uk-text-small">@lang('Title')</label>
                <div class="uk-margin-bottom">
                    <cp-field field="text" bind="meta.title" cls="uk-form-large"></cp-field>
                </div>

                <label class="uk-text-small">@lang('Description')</label>
                <div class="uk-margin-bottom">
                    <cp-field field="textarea" bind="meta.description" cls="uk-form-large"></cp-field>
                </div>

                <label class="uk-text-small">@lang('Keywords')</label>
                <div class="uk-margin-bottom">
                    <cp-field field="text" bind="meta.keywords" cls="uk-form-large"></cp-field>
                </div>

                <label class="uk-text-small">@lang('Author')</label>
                <div class="uk-margin-bottom">
                    <cp-field field="text" bind="meta.author" cls="uk-form-large"></cp-field>
                </div>

                <div class="uk-margin-large-top">
                    <button class="uk-button uk-button-large uk-button-primary uk-width-medium-1-4 uk-margin-small-right">@lang('Save')</button>
                    <a href="@route('/coco')">Cancel</a>
                </div>

            </div>
            <div class="uk-width-1-3">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-text-large">CoCo</div>
                    <span class="uk-badge uk-margin-small-top">{{ $info->version }}</span>
                    <br><br>
                    <strong>@lang('Licensed to')</strong>

                    @if($license = $app->module('coco')->getLicense())
                        <div class="uk-margin">
                            {{ $license['name']}}<br>
                            <p class="uk-text-muted uk-text-small">
                                <span class="uk-text-uppercase">{{ $license['type']}}</span><br>
                                {{ $license['email']}}<br>
                                {{ $license['company']}}
                            </p>
                            <hr class="uk-width-1-2">
                            <p class="uk-text-small">
                                <i class="uk-icon-heart uk-text-danger uk-margin-small-right"></i>
                                Thank you for being awesome and your support!
                            </p>
                        </div>
                    @else
                        <p class="uk-text-danger"><i class="uk-icon-warning uk-margin-small-right"></i> @lang('Unlicensed version. Enjoy your trial.')</p>
                    @endif
                </div>
            </div>
        </div>
    </form>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.meta = {{ json_encode($meta) }};

        save() {

            App.request('/coco/utils/updateSettings', {settings: this.meta}).then(function(data) {
                App.ui.notify("Settings updated", "success");
            });
        }

    </script>
</div>
