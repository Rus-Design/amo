{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
 *  @author    Rus-Design info@rus-design.com
 *  @copyright 2020 Rus-Design
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  Property of Rus-Design
*}

<div class="panel">
	<div class="row moduleconfig-header">
		<div class="col-xs-5 text-right">
			<img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo.jpg" />
		</div>
		<div class="col-xs-7 text-left">
			<h2>{l s='Amocrm module integration' mod='amo'}</h2>
			<h4>{l s='Send data all you need from your shop' mod='amo'}</h4>
		</div>
		<div class="col-xs-7 text-left">
			<span>{l s='Other modules ' mod='bitrix24'}</span><a href="https://rus-design.com/en/?{Context::getContext()->shop->getBaseURL(true)}" target="_blank">https://rus-design.com/en/</a>
		</div>
	</div>

	<hr />

	<div class="moduleconfig-content">
		<div class="row">
			<div class="col-xs-12">
				<p>
					<h4>{l s='Send all you need informartion of order and customer' mod='amo'}</h4>
				</p>
			</div>
		</div>
	</div>
</div>
