<script type="riot/tag" src="@base('copilot:assets/components/qrcode.html')"></script>

<ul  class="uk-breadcrumb">
    @render('copilot:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Settings')</span></li>
</ul>

<div class="uk-form" riot-view>

    <form onsubmit="{ save }">

        <div class="uk-grid">

            <div class="uk-grid-margin uk-width-medium-1-2">

                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-margin uk-flex">
                        <span class="uk-text-large uk-flex-item-1">Copilot</span>
                        <span class="uk-badge uk-margin-small-top uk-flex uk-flex-middle"><span>{{ $info->version }}</span></span>
                    </div>

                    <div class="uk-flex">
                        <div class="uk-margin-right">
                            <img class="uk-svg-adjust uk-text-{{ $license->type != 'trial' ? 'success':'muted'}}" src="@base('copilot:assets/media/icons/license.svg')" width="50" alt="License" data-uk-svg>
                        </div>
                        <div class="uk-flex-item-1">
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

                            <span class="uk-badge uk-badge-danger">@lang('Free Trial')</span>

                            <div class="uk-margin-small-top uk-text-muted">
                                @lang('Unlicensed version. Enjoy your free trial.') 
                                <a href="http://getcockpit.com" target="_blank">@lang('Buy a license')</a>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <div class="uk-grid-margin uk-width-medium-1-2 uk-flex-order-first-medium">

                <h3>@lang('Meta Information')</h3>

                <label class="uk-text-small">@lang('Title')</label>
                <div class="uk-margin-bottom">
                    <input type="text" bind="meta.title" class="uk-form-large uk-width-1-1">
                </div>

                <label class="uk-text-small">@lang('Description')</label>
                <div class="uk-margin-bottom">
                    <textarea field="textarea" bind="meta.description" class="uk-form-large uk-width-1-1" style="height:150px;"></textarea>
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
