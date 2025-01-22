<div>
    <div class="container">
        <form enctype="multipart/form-data" method="post" class="form-info" data-actions='{"keydown":"form_keydown","submit":"form_submit"}'>
            <div class="row text-center">
                <div class="col-md-8 offset-md-2">
                    <h4 class="font-weight-bold title-form">TEAMCO Roster Form</h4>
                    <p class="font-weight-bold sub-titles-form">Before completing your Roster Form, please note:</p>
                    <p class="font-weight-bold font-italic color-blue">Please only complete the Roster Form to submit an order. If instead you would like to get a price quote, please contact us.</p>
                    <p class="font-italic">The completed Roster Form is the primary document for production of your order. As such, we strongly recommend that your Roster Form only be submitted when all your roster details are completely finalized. Once submitted, any subsequent changes to your Roster may result in a delay to the order.</p>
                    <p class="font-italic">All fields marked with an asterisk (*) are required</p>
                    @if($rosterStaticFiles['view_sample']['display'])
                    <a id="js_view_sample" href="{!! $rosterStaticFiles['view_sample']['url'] !!}" target="_blank" class="mt-3 hidden">View Sample</a>
                    @endif
                </div>
            </div>
            <div class="row mt-3 text-center">
                <div class="col-md-6 offset-md-3">
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">1. (a) Contact and Shipping Information</span></p>
                    <div class="form-group"><input type="text" placeholder="Web Inquiry # or PO# (enter N/A if not applicable)*" required="required" class="form-control" tabindex="0"></div>
                    <div class="form-group"><input type="text" placeholder="Name*" required="required" class="form-control" tabindex="1"></div>
                    <div class="form-group"><input type="text" placeholder="Company / Organization" class="form-control" tabindex="2"></div>
                    <div class="form-group"><input type="text" id="autocomplete" placeholder="Address*" required="required" class="form-control pac-target-input" autocomplete="off" tabindex="3"></div>
                    <div class="form-group"><input type="text" placeholder="Address 2" class="form-control" tabindex="4"></div>
                    <div class="form-group"><input type="text" placeholder="City*" required="required" class="form-control" tabindex="5"></div>
                    <div class="form-group">
                        <select name="" id="" required="required" class="form-control" tabindex="6">
                            <option value="">Prov / State*</option>
                            @foreach($countryStates as $country)
                                <optgroup label="{!! $country['name'] !!}">
                                    @foreach($country['states'] as $state)
                                        <option value="{!! $state->state_code !!}">{!! $state->name !!}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><input type="text" placeholder="Postal Code*" required="required" class="form-control" tabindex="7"></div>
                    <div class="form-group"><input type="text" placeholder="Country*" required="required" class="form-control" tabindex="8"></div>
                    <div class="form-group"><input type="email" placeholder="Email*" required="required" autocomplete="on" class="form-control" tabindex="9"></div>
                    <div class="form-group"><input type="email" placeholder="Confirm Email*" required="required" autocomplete="on" id="js_confirm_email" class="form-control" tabindex="10"></div>
                    <div class="form-group"><input type="text" placeholder="Phone" class="form-control" tabindex="11"></div>
                    <div class="text-center">
                        <span class="itl">Do you have a separate billing address?</span>
                        <label for="billing-fields-hide" class="mx-2">
                            <input type="radio" id="billing-fields-hide" checked="checked" name="billing-fields" value="0" class="mx-2">No
                        </label>
                        <label for="billing-fields-show" class="mx-2">
                            <input type="radio" id="billing-fields-show" name="billing-fields" value="1" class="mx-2">Yes
                        </label>
                    </div>
                    <p class="mt-4 mb-2 billing-field-group"><span class="font-weight-bold sub-titles-form">1. (b) Billing Information:</span></p>
                    <p class="text-center billing-field-group"><em class="itl">(skip if same as shipping address)</em></p>
                    <div class="form-group billing-field-group"><input type="text" placeholder="Name" class="form-control" tabindex="12"></div>
                    <div class="form-group billing-field-group"><input type="text" placeholder="Company / Organization" class="form-control" tabindex="13"></div>
                    <div class="form-group billing-field-group"><input type="text" id="billing_autocomplete" placeholder="Address" class="form-control pac-target-input" autocomplete="off" tabindex="14"></div>
                    <div class="form-group billing-field-group"><input type="text" placeholder="Address 2" class="form-control" tabindex="15"></div>
                    <div class="form-group billing-field-group"><input type="text" placeholder="City" class="form-control" tabindex="16"></div>
                    <div class="form-group billing-field-group">
                        <select name="" id="" class="form-control" tabindex="17">
                            <option value="">Prov / State</option>
                            @foreach($countryStates as $country)
                                <optgroup label="{!! $country['name'] !!}">
                                    @foreach($country['states'] as $state)
                                        <option value="{!! $state->state_code !!}">{!! $state->name !!}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group billing-field-group"><input type="text" placeholder="Postal Code" class="form-control" tabindex="18"></div>
                    <div class="form-group billing-field-group"><input type="text" placeholder="Country" class="form-control" tabindex="19"></div>
                    <div class="form-group billing-field-group"><input type="email" placeholder="Email" autocomplete="on" class="form-control" tabindex="20"></div>
                    <div class="form-group billing-field-group"><input type="text" placeholder="Phone" class="form-control" tabindex="21"></div>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">2. Shipping Method</span></p>
                    <div class="text-center team-roster">
                        <p>For customers in Canada, please choose the shipping method for your order.<br>Note - For customers in USA, please skip this section.</p>
                        <p>Note - Shipping transit times and costs can be estimated using our <a href="/pages/calculate-shipping-rates/" target="_blank">Shipping Calculator</a>.</p>
                    </div>
                    <div class="form-group">
                        <select name="" id="" class="form-control" tabindex="22">
                            <option value="">No Preference - Teamco will choose*</option>
                            @foreach($shippingServices as $service)
                                <option value="{!! $service['name'] !!}">{!! $service['name'] !!}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">3. Jersey Details</span></p>
                    <div class="form-group"><input type="text" placeholder="Style Code*" required="required" class="form-control" tabindex="23"></div>
                    <div class="form-group"><input type="text" placeholder="Color 1" class="form-control" tabindex="24"></div>
                    <div class="form-group"><input type="text" placeholder="Color 2" class="form-control" tabindex="25"></div>
                    <div class="form-group"><input type="text" placeholder="Color 3" class="form-control" tabindex="26"></div>
                    <div class="form-group"><input type="text" placeholder="Color 4" class="form-control" tabindex="27"></div>
                    <div class="form-group"><input type="text" placeholder="Color 5" class="form-control" tabindex="28"></div>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">4. Accessory Items</span></p>
                    <p class="text-center"><em class="itl">Please list any other items that are also part of your order (e.g. matching shorts, hockey socks, etc.)</em></p>
                    <div class="form-group"><input type="text" placeholder="Accessory Items" class="form-control" tabindex="29"></div>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">5. Number Colors</span></p>
                    <div class="form-group"><input type="text" placeholder="Number Colors" class="form-control" tabindex="30"></div>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">6. Artwork Placement and Order Description</span></p>
                    <p class="text-left font-italic font-weight-bold mb-1">Please describe in point form, and as clearly as possible:</p>
                    <p class="text-left mb-1">(a) the artwork you would like on the jerseys, where it should be placed and any other relevant information;</p>
                    <p class="text-left mb-2">(b) the correct spelling of your Team Name (if applicable)</p>
                    <div class="mb-1">
                        @if($rosterStaticFiles['artwork_placement_guide']['display'])
                        <label id="js_artwork_placement_guide" class="artwork-link">
                            <em><a href="{!! $rosterStaticFiles['artwork_placement_guide']['url'] !!}" target="_blank">(View our artwork placement guide)</a></em>
                        </label>
                        @endif
                        <textarea cols="20" rows="5" required="required" placeholder="Please write in point form and as clearly as possible." class="form-control text-area" tabindex="31"></textarea>
                    </div>
                    <p class="mt-4 mb-1"><span class="font-weight-bold sub-titles-form">7. If you are ordering multiple sets of jerseys - please note:</span></p>
                    <p class=""><span class="font-italic">(skip if N/A)</span></p>
                    <p class=""><span class="font-italic">If the roster below is to be used for multiple sets of jerseys, please write the name of each set in the next section.</span></p>
                    <p class=""><span class="font-italic">If you are ordering multiple sets of jerseys - which have different rosters - please complete a separate Roster Form for each set, and write the applicable Set Name in the next section.</span></p>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">8. Jersey Quantities Per Set</span></p>
                    <p id="js_qty_alert_jersey" class="js_qty_alert hidden"><span class="red">Please fill out this section.</span></p>
                    <table id="js_qty_table_jersey" class="js_qty_table table table-bordered custom-table mb-3">
                        <thead>
                            <tr>
                                <th colspan="2" class="p-0">
                                    <input type="text" placeholder="Enter Set Name(s) if applicable" class="form-control border-0" tabindex="32">
                                </th>
                            </tr>
                            <tr>
                                <th>Size</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sizes as $size)
                                <tr>
                                    <td>{!! $size['name'] !!}</td>
                                    <td><input type="number" min="0" data-parent="#js_qty_table_jersey" data-alert="#js_qty_alert_jersey" class="js_qty_field form-control hide-appearance"></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>Total</td>
                                <td><input type="text" readonly="readonly" class="form-control" tabindex="33">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">9. Shorts or Socks Quantities Per Set</span></p>
                    <p id="js_qty_alert_shorts" class="js_qty_alert hidden"><span class="red">Please fill out this section.</span></p>
                    <table id="js_qty_table_shorts" class="js_qty_table table table-bordered custom-table mb-3">
                        <thead>
                            <tr>
                                <th colspan="2" class="p-0">
                                    <input type="text" placeholder="Enter Set Name(s) if applicable" class="form-control border-0" tabindex="34">
                                </th>
                            </tr>
                            <tr>
                                <th>Size</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sizes as $size)
                                <tr>
                                    <td>{!! $size['name'] !!}</td>
                                    <td><input type="number" min="0" data-parent="#js_qty_table_shorts" data-alert="#js_qty_alert_shorts" class="js_qty_field form-control hide-appearance"></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>Total</td>
                                <td><input type="text" readonly="readonly" class="form-control" tabindex="35">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-8 offset-md-2">
                    <p class="my-4"><span class="font-weight-bold sub-titles-form">10. Team Roster</span></p>
                    <div class="text-center team-roster">
                        @if($rosterStaticFiles['excel_roster_form']['display'])
                        <p id="js_excel_roster_form" class="">For teams over 30 players, please use our <a href="{!! $rosterStaticFiles['excel_roster_form']['url'] !!}" target="_blank" download="download">Excel Roster Form</a></p>
                        @endif
                        <p class="color3 font-weight-bold">**We highly recommend consulting the Size Chart, before choosing your sizes. Please contact us if you would like us to send you the applicable size chart for your jerseys.**</p>
                        <p class="color2 font-weight-bold">Please enter names in UPPERCASE.</p>
                        <p class="color2">For volleyball libero, hockey goalie, soccer goalie, or lacrosse goalie jerseys: please let us know the size in the Notes column.</p>
                    </div>
                    <table class="table table-bordered custom-table-2 mb-3">
                        <thead>
                            <tr>
                                <th width="30">-</th>
                                <th width="*">Jersey Size</th>
                                <th width="*" class="text-nowrap">Jersey #</th>
                                <th width="50%">Jersey Name</th>
                                <th width="50%">Notes</th>
                                <th width="*">Shorts Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i=1; $i<=40; $i++)
                            <tr>
                                <td>{!! $i !!}.</td>
                                <td>
                                    <select name="" id="" class="form-control select-form-control" tabindex="36">
                                        <option value="false">--</option>
                                        {!! $sizesHtml !!}
                                    </select>
                                </td>
                                <td><input type="text" class="form-control" tabindex="37"></td>
                                <td><input type="text" class="form-control" tabindex="38"></td>
                                <td><input type="text" class="form-control" tabindex="39"></td>
                                <td>
                                    <select name="" id="" class="form-control select-form-control" tabindex="40">
                                        <option value="false">--</option>
                                        {!! $sizesHtml !!}
                                    </select>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <p class="my-4"><span class="font-weight-bold sub-titles-form">11. Attach Logo(s)</span></p>
                        <p class="font-weight-bold">Please attach your logos. For non-vector logos (e.g. PNG, JPEG, etc.) conversion charges may apply.</p>
                        <button type="button" class="btn btn-secondary">Choose Files</button>
                        <div class="col-md-12 mt-3"><!----></div>
                        <div>
                            <div id="modal1___BV_modal_outer_" style="position: absolute; z-index: 1040;">
                                <div id="modal1" role="dialog" tabindex="-1" aria-hidden="true"
                                     class="modal fade" style="display: none;">
                                    <div class="modal-dialog modal-lg">
                                        <div role="document" id="modal1___BV_modal_content_"
                                             aria-labelledby="modal1___BV_modal_header_"
                                             aria-describedby="modal1___BV_modal_body_"
                                             class="modal-content">
                                            <header id="modal1___BV_modal_header_" class="modal-header"><h5
                                                    class="modal-title">List of Files</h5>
                                                <button type="button" aria-label="Close" class="close">×
                                                </button>
                                            </header>
                                            <div id="modal1___BV_modal_body_" class="modal-body">
                                                <div class="form-group"><input type="file" id="files"
                                                                               multiple="multiple"
                                                                               class="form-control form-upload">
                                                </div>
                                                <table class="table text-center">
                                                    <thead>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Name</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td colspan="3">Currently no files have been
                                                            added.
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <footer id="modal1___BV_modal_footer_" class="modal-footer">
                                                <button type="button" class="btn btn-secondary">Cancel
                                                </button>
                                                <button type="button" class="btn btn-primary">OK</button>
                                            </footer>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <p class="font-weight-bold">What Happens Next?</p>
                        <p>
                            Once we receive your Roster Form we will review it to ensure all information is
                            complete. We will then email you a digital proof containing all order details
                            for you to approve. Your order will go into production after your approval is
                            received and payment is processed.
                            <span class="font-weight-bold">Please note - any roster changes after the Roster Form is submitted may result in a delay to your order.</span>
                        </p>
                        <div class="form-group">
                            <div class="center-recaptcha">
                                <div style="width: 304px; height: 78px;">
                                    <div>
                                        <iframe title="reCAPTCHA" width="304" height="78"
                                                role="presentation" name="a-u21not7vuf7" frameborder="0"
                                                scrolling="no"
                                                sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation allow-modals allow-popups-to-escape-sandbox allow-storage-access-by-user-activation"
                                                src="https://www.google.com/recaptcha/api2/anchor?ar=2&amp;k=6Lc_uJoUAAAAAJ6jvMERrmHg9-6iVgRQk5wMFuj5&amp;co=aHR0cHM6Ly90ZWFtY29zcG9ydHN3ZWFyLmNvbTo0NDM.&amp;hl=en&amp;v=zIriijn3uj5Vpknvt_LnfNbF&amp;size=normal&amp;cb=mtu1x4v9d67m"></iframe>
                                    </div>
                                    <textarea id="g-recaptcha-response" name="g-recaptcha-response"
                                              class="g-recaptcha-response"
                                              style="width: 250px; height: 40px; border: 1px solid rgb(193, 193, 193); margin: 10px 25px; padding: 0px; resize: none; display: none;"></textarea>
                                </div>
                                <iframe style="display: none;"></iframe>
                            </div>
                            <p class="text-danger text-center" style="display: none;">Please resolve the Captcha</p></div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary-custom btn-lg btn-block btn-primary">Submit</button>
                        </div>
                    </div>
                    <div class="form-group hidden">
                        <input type="hidden" class="form-control hidden" value="dev">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
