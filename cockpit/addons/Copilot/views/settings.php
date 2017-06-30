<script type="riot/tag" src="@base('copilot:assets/components/qrcode.html')"></script>

<ul  class="uk-breadcrumb">
    @render('copilot:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Settings')</span></li>
</ul>

<div class="uk-form" riot-view>

    <form onsubmit="{ save }">

        <div class="uk-grid">

            <div class="uk-grid-margin uk-width-medium-1-3">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-margin uk-flex">
                        <span class="uk-text-large uk-flex-item-1">Copilot</span>
                        <span class="uk-badge uk-margin-small-top uk-flex uk-flex-middle"><span>{{ $info->version }}</span></span>
                    </div>
                    
                    @if($license->type != 'trial')

                    <div class="uk-margin">
                        <strong>@lang('Licensed to')</strong>
                    </div>

                    <div class="uk-margin">

                        <p>
                            {{ $license->name }}<br>
                            <span class="uk-text-small">{{ @$license->domain }}</span>
                        </p>

                        <div class="uk-grid uk-margin">
                            <div class="uk-width-3-4">
                                <p class="uk-text-muted uk-text-small">
                                    <span class="uk-text-uppercase">{{ $license->type}}</span><br>
                                    {{ $license->email}}<br>
                                    {{ $license->company }}
                                </p>
                            </div>
                            <div class="uk-width-1-4">
                                <qrcode text="Thank you!"></qrcode>
                            </div>
                        </div>

                        <hr class="uk-width-1-2">
                        <p class="uk-text-small">
                            <i class="uk-icon-heart uk-text-danger uk-margin-small-right"></i>
                            Thank you for being awesome and your support!
                        </p>
                    </div>

                    @else

                    <div class="uk-margin uk-alert uk-alert-warning">
                        <p><i class="uk-icon-warning uk-margin-small-right"></i> @lang('Unlicensed version. Enjoy your trial.')</p>
                    </div>

                    <a href="http://cocopi.co" target="_blank">@lang('Buy a license')</a>

                    @endif
                </div>
            </div>

            <div class="uk-grid-margin uk-width-medium-2-3 uk-flex-order-first-medium"">

                <label class="uk-text-small">@lang('Title')</label>
                <div class="uk-margin-bottom">
                    <input type="text" bind="meta.title" class="uk-form-large uk-width-1-1">
                </div>

                <label class="uk-text-small">@lang('Description')</label>
                <div class="uk-margin-bottom">
                    <textarea field="textarea" bind="meta.description" class="uk-form-large uk-width-1-1"></textarea>
                </div>

                <label class="uk-text-small">@lang('Keywords')</label>
                <div class="uk-margin-bottom">
                    <input type="text" bind="meta.keywords" class="uk-form-large uk-width-1-1">
                </div>

                <label class="uk-text-small">@lang('Author')</label>
                <div class="uk-margin-bottom">
                    <input type="text" bind="meta.author" class="uk-form-large uk-width-1-1">
                </div>

                <div class="uk-margin-large-top">
                    <button class="uk-button uk-button-large uk-button-primary uk-width-medium-1-2 uk-width-medium-1-4 uk-margin-small-right">@lang('Save')</button>
                </div>

            </div>
            
        </div>
    </form>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.meta = {{ json_encode($meta) }};

        save(e) {

            e.preventDefault();

            App.request('/copilot/utils/updateSettings', {settings: this.meta}).then(function(data) {
                App.ui.notify("Settings updated", "success");
            });
        }

    </script>
</div>
