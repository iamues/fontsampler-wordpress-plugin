<h1><?php echo empty( $set['id'] ) ? 'New fontsampler' : 'Edit fontsampler ' . $set['id'] ?></h1>
<p>Once you create the fontsampler, it will be saved with an ID you use to embed it on your wordpress pages</p>
<form method="post" enctype="multipart/form-data" action="?page=fontsampler" id="fontsampler-edit-sample">
	<input type="hidden" name="action" value="edit_set">
	<?php if ( function_exists( 'wp_nonce_field' ) ) : wp_nonce_field( 'fontsampler-action-edit_set' ); endif; ?>
	<?php if ( ! empty( $set['id'] ) ) : ?><input type="hidden" name="id"
	                                              value="<?php echo $set['id']; ?>"><?php endif; ?>

	<h2>Fonts</h2>

	<?php if ( ! empty ( $fonts ) ): ?>
		<p>Pick which font sets to use, or upload your fontsampler's fonts now:</p>
	<?php endif; ?>

	<input type="hidden" name="fonts_order" value="<?php if ( ! empty( $fonts_order ) ) : echo $fonts_order; endif; ?>">
	<ul id="fontsampler-fontset-list">
		<?php // looop through all pased in $set['fonts'] ?>
		<?php if ( ! empty( $set['id'] ) && ! empty( $set['fonts'] ) ) : foreach ( $set['fonts'] as $existing_font ) : ?>
			<li>
				<span class="fontsampler-fontset-sort-handle">&varr;</span>
				<select name="font_id[]">
					<option value="0">-pick from existing-</option>
					<?php // for each dropdown loop print out all fonts and if the current font is in the set, select it ?>
					<?php foreach ( $fonts as $font ) : ?>
						<option <?php if ( in_array( $existing_font['name'], $font ) ) : echo ' selected="selected"'; endif; ?>
							value="<?php echo $font['id']; ?>">
							<?php echo $font['name']; ?>
						</option>
					<?php endforeach; ?>
				</select>
				<button class="btn btn-small fontsampler-fontset-remove">&minus;</button>
				<div class="fontsampler-initial-font-selection">
					<input type="radio" name="initial_font" value="<?php echo $existing_font['id']; ?>"
						<?php if ( ! isset( $set['initial_font'] ) || $set['initial_font'] == $existing_font['id'] ) :
						echo 'checked="checked"'; ?>>
					<span class="fontsampler-initial-font selected">
							<span class="initial-font-selected">Is initially selected</span>
							<span class="initial-font-unselected">Set as initial</span>
						</span>
					<?php else : ?>
						><span class="fontsampler-initial-font">
							<span class="initial-font-selected">Is initially selected</span>
							<span class="initial-font-unselected">Set as initial</span>
						</span><?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>

		<?php elseif ( empty( $set['id'] ) && ! empty( $fonts ) ) : ?>
			<li>
				<!-- for a new fontset, display one, non-selected, select choice -->
				<span class="fontsampler-fontset-sort-handle">&varr;</span>
				<select name="font_id[]">
					<option value="0">-pick from existing-</option>
					<?php foreach ( $fonts as $font ) : ?>
						<option value="<?php echo $font['id']; ?>"><?php echo $font['name']; ?></option>
					<?php endforeach; ?>
				</select>
				<button class="btn btn-small fontsampler-fontset-remove"
				        title="Remove this fontset from sampler">&minus;</button>

				<div class="fontsampler-initial-font-selection">
					<input type="radio" name="initial_font" value="" checked="checked">
					<span class="fontsampler-initial-font selected">
						<span class="initial-font-selected">Is initially selected</span>
						<span class="initial-font-unselected">Set as initial</span>
					</span>
				</div>
			</li>
		<?php endif; ?>
	</ul>

	<?php if ( ! empty( $fonts ) ): ?>
		<p>
			<button class="btn btn-small fontsampler-fontset-add">+</button>
			<span>Add existing fontset</span>
		</p>
	<?php endif; ?>
	<p>
		<button class="btn btn-small fontsampler-fontset-create-inline">+</button>
		<span>Add new fontset and upload fonts</span>
	</p>

	<small>Picking multiple font set will enable the select field for switching between fonts used in the
		Fontsampler.
	</small>
	<br>
	<small>Use the arrow on the left to drag the order of the fonts. Use the minus on the right to remove fonts.</small>


	<h2>Interface options</h2>
	<h3>Initial text</h3>
	<div style="overflow: hidden;">
		<div class="fontsampler-admin-column-half">
			<label>
				<span>The initial text displayed in the font sampler, for example the font name or pangram. You can use multi-line text here as well.</span><br>
				<textarea name="initial" rows="5"
				          dir="<?php echo ( ! isset( $set['is_ltr'] ) || $set['is_ltr'] == "1" ) ? 'ltr' : 'rtl'; ?>"><?php if ( ! empty( $set['initial'] ) ) : echo $set['initial']; endif; ?></textarea>
			</label>
		</div>
		<div class="fontsampler-admin-column-half">
			<p>Fontsampler script direction is:</p>
			<label><input type="radio" name="is_ltr" value="1"
					<?php if ( empty( $set['is_ltr'] ) || $set['is_ltr'] == "1" ) : echo 'checked="checked"'; endif; ?>>
				<span>Left to Right</span></label>
			<label><input type="radio" name="is_ltr" value="0"
					<?php if ( isset( $set['is_ltr'] ) && $set['is_ltr'] == "0" ) : echo 'checked="checked"'; endif; ?>>
				<span>Right to Left</span></label>
		</div>
	</div>

	<div style="overflow: hidden;">
		<div class="fontsampler-options-checkbox">
			<fieldset>
				<legend>Common features</legend>

				<label>
					<input data-toggle-class="use-defaults" data-toggle-id="fontsampler-options-checkboxes" type="radio"
					       name="default_features" value="1"
						<?php if ( $set['default_features'] ): echo 'checked="checked"'; endif; ?>>
					<span>Use default features</span>
				</label>
				<label>
					<input data-toggle-class="use-defaults" data-toggle-id="fontsampler-options-checkboxes" type="radio"
					       name="default_features" value="0"
						<?php if ( ! $set['default_features'] ): echo 'checked="checked"'; endif; ?>>
					<span>Select custom features</span>
				</label>

				<div id="fontsampler-options-checkboxes"
				     class="<?php echo $set['default_features'] ? 'use-defaults' : ''; ?> ">
					<?php
					$options = $set;
					include( 'fontsampler-options.php' );
					?>
				</div>
			</fieldset>
		</div>


		<fieldset>
			<legend>Links</legend>
			<small>Leave the field empty to not include a buy or specimen button in the interface. Provide full URLs
				starting with <code>http://</code>.
				<br>
				Note: You can also link to files, like PDFs.
			</small>
			<label>
				<span class="input-description">"Buy" URL for this fontsampler:</span>
				<input type="text" name="buy" class="fontsampler-admin-slider-label"
				       value="<?php if ( isset( $set['buy'] ) ): echo $set['buy']; endif; ?>"/>
			</label>
			<label>
				<span class="input-description">"Speciment" URL for this fontsampler:</span>
				<input type="text" name="specimen" class="fontsampler-admin-slider-label"
				       value="<?php if ( isset( $set['specimen'] ) ): echo $set['specimen']; endif; ?>"/>
			</label>
		</fieldset>
	</div>

	<h2>Interface layout</h2>

	<div class="fontsampler-admin-column-wrapper">
		<div class="fontsampler-admin-column-half">
			<p>You can customize the layout of interface elements to differ from the defaults.
				Simply hover any element, then <strong style="background: orange; ">DRAG &amp; DROP the orange
					block</strong> into a new position.
			</p>
			<small>Only options you have enabled above will be available for sorting in this preview.</small>
			<br>
			<small>Note that the font displayed below is only a placeholders to render the interface.</small>
		</div>
		<div class="fontsampler-admin-column-half">
			<div id="fontsampler-ui-layout-preview-options">
				<p>You can select the Fontsamplers column layout here. The size of the Fontsampler always depends
					on its container in the page layout, so Fontsamplers might have an different overall size when
					rendered
					in your site's theme. The columns work proportionally and scale with the Fontsampler's
					container.</p>
				<?php
				if ( ! isset( $set['ui_columns'] ) ): $set['ui_columns'] = 3; endif;
				?>
				<label class="fontsampler-admin-label-block">
					<input type="radio" value="1" name="ui_columns"
						<?php if ( $set['ui_columns'] == 1 ) {
							echo ' checked="checked" ';
						} ?>>1 column
					<small>(For mobile devices this will automatically be used as fallback)</small>
				</label>
				<label class="fontsampler-admin-label-block">
					<input type="radio" value="2" name="ui_columns"
						<?php if ( $set['ui_columns'] == 2 ) {
							echo ' checked="checked" ';
						} ?>>2 columns
				</label>
				<label class="fontsampler-admin-label-block">
					<input type="radio" value="3" name="ui_columns"
						<?php if ( $set['ui_columns'] == 3 ) {
							echo ' checked="checked" ';
						} ?>>3 columns
				</label>
				<label class="fontsampler-admin-label-block">
					<input type="radio" value="4" name="ui_columns"
						<?php if ( $set['ui_columns'] == 4 ) {
							echo ' checked="checked" ';
						} ?>>4 columns
				</label>
			</div>
		</div>
	</div>

	<?php
	$layout = new FontsamplerLayout();
	?>

	<div id="fontsampler-admin-ui-wrapper">
		<input name="ui_order" type="hidden"
		       value="<?php if ( ! empty( $set['ui_order'] ) ) :
			       echo $layout->sanitizeString( $set['ui_order'], $set );
		       else:
			       echo $layout->arrayToString( $layout->getDefaultBlocks(), $set );
		       endif; ?>">

		<ul id="fontsampler-ui-blocks-list">

			<?php
			$blocks = array_merge( $layout->getDefaultBlocks(), $layout->stringToArray( $set['ui_order'], $set ) );

			foreach ( $blocks as $item => $class ) : ?>
				<li>
					<div class="fontsampler-ui-block-overlay"
					     data-item="<?php echo $item; ?>"
					     data-default-class="<?php echo $layout->blocks[ $item ][0]; ?>">
						<button class="fontsampler-ui-block-settings">
							<img src="<?php echo plugin_dir_url( __FILE__ ); ?>../icons/settings.svg">
						</button>
						<div class="fontsampler-ui-block-options">
							<button class="fontsampler-ui-block-add-break">Insert row break after</button>
							<div class="fontsampler-ui-block-layout-classes">
								<?php echo $layout->labels[ $item ]; ?> -block layout:
								<?php foreach ( $layout->blocks[ $item ] as $cl ): ?>
									<label><input type="radio"
									              value="<?php echo $cl; ?>"
									              data-target="<?php echo $item; ?>"
									              name="layout_class_<?php echo $item; ?>"
											<?php if ( $cl === $class ) {
												echo ' checked="checked" ';
											} ?>><span><?php echo $cl; ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</li>

				<?php
			endforeach;
			?>
		</ul>

		<!-- gets populated with the actual fontsampler instance loaded via ajax -->
		<div id="fontsampler-ui-layout-preview"></div>

	</div>
	<br>
	<?php submit_button(); ?>
</form>


<!-- hidden templates -->
<div class="fontsampler-admin-placeholders">
	<ul>
		<li id="fontsampler-admin-fontpicker-placeholder">
			<!-- for a new fontset, display one, non-selected, select choice -->
			<span class="fontsampler-fontset-sort-handle">&varr;</span>
			<select name="font_id[]">
				<option value="0">-pick from existing-</option>
				<?php foreach ( $fonts as $font ) : ?>
					<option value="<?php echo $font['id']; ?>"><?php echo $font['name']; ?></option>
				<?php endforeach; ?>
			</select>
			<button class="btn btn-small fontsampler-fontset-remove"
			        title="Remove this fontset from sampler">&minus;</button>

			<div class="fontsampler-initial-font-selection">
				<input type="radio" name="initial_font" value="">
				<span class="fontsampler-initial-font">
						<span class="initial-font-selected">Is initially selected</span>
						<span class="initial-font-unselected">Set as initial</span>
					</span>
			</div>
		</li>
		<li id="fontsampler-fontset-inline-placeholder" class="fontsampler-fontset-inline">
			<input class="inline_font_id" value="" type="hidden">
			<span class="fontsampler-fontset-sort-handle">&varr;</span>

			<div class="fontsampler-fontset-inline-wrapper">
				<?php
				unset( $font );
				include( 'fontset-fonts.php' );
				?>
				<small style="float: left;">Uploading at the very least a <code>woff</code> file is recommended.
					You can later edit the files of this fontset in the
					<a href="?page=fontsampler&subpage=fonts">Fonts &amp; Files</a> tab.
				</small>
			</div>
			<button class="btn btn-small fontsampler-fontset-remove"
			        title="Remove this fontset from sampler">&minus;</button>

			<div class="fontsampler-initial-font-selection">
				<input type="radio" name="initial_font" value="">
				<span class="fontsampler-initial-font">
						<span class="initial-font-selected">Is initially selected</span>
						<span class="initial-font-unselected">Set as initial</span>
					</span>
			</div>
		</li>
	</ul>
</div>


