<h2 style="margin-top:14px">State where the project is</h2>
<input class="inv-input" type="text" name="location_state" maxlength="100" required value="{{ old('location_state') }}">
<h2 style="margin-top:14px">Project address or site location</h2>
<input class="inv-input" type="text" name="location_address" maxlength="255" required value="{{ old('location_address') }}">
<h2 style="margin-top:14px">CAC registration number</h2>
<input class="inv-input" type="text" name="cac_number" maxlength="40" required value="{{ old('cac_number') }}">
<h2 style="margin-top:14px">Exactly how the money will be used</h2>
<textarea class="inv-input" name="use_of_funds" rows="4" maxlength="2000" required>{{ old('use_of_funds') }}</textarea>
<h2 style="margin-top:14px">Risks investors should know about</h2>
<textarea class="inv-input" name="risk_disclosure" rows="4" maxlength="2000" required>{{ old('risk_disclosure') }}</textarea>
<p class="inv-muted">An admin will check your ID, company, directors, site and permits before this opens. You will need to provide proof when asked.</p>
