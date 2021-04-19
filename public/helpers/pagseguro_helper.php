<?php
function ps_paymentMethodType($id) {
	$method = $id;
	switch ($id) {
		case 1:		$method = 'Cartão de crédito';		break;
		case 2:		$method = 'Boleto';					break;
		case 3:		$method = 'Débito online (TEF)';	break;
		case 4:		$method = 'Saldo PagSeguro:';		break;
		case 5:		$method = 'Oi Paggo';				break;
		case 7:		$method = 'Depósito em conta';		break;
		case 11:	$method = 'PIX';					break;
	}

	return $method;
}

function ps_paymentMethodCode($id) {
	$method = $id;
	switch ($id) {
		case 101:	$method = 'Cartão de crédito Visa';					break;
		case 102:	$method = 'Cartão de crédito MasterCard';			break;
		case 103:	$method = 'Cartão de crédito American Express';		break;
		case 104:	$method = 'Cartão de crédito Diners';				break;
		case 105:	$method = 'Cartão de crédito Hipercard';			break;
		case 106:	$method = 'Cartão de crédito Aura';					break;
		case 107:	$method = 'Cartão de crédito Elo';					break;
		case 108:	$method = 'Cartão de crédito PLENOCard';			break;
		case 109:	$method = 'Cartão de crédito PersonalCard';			break;
		case 110:	$method = 'Cartão de crédito JCB';					break;
		case 111:	$method = 'Cartão de crédito Discover';				break;
		case 112:	$method = 'Cartão de crédito BrasilCard';			break;
		case 113:	$method = 'Cartão de crédito FORTBRASIL';			break;
		case 114:	$method = 'Cartão de crédito CARDBAN';				break;
		case 115:	$method = 'Cartão de crédito VALECARD';				break;
		case 116:	$method = 'Cartão de crédito Cabal';				break;
		case 117:	$method = 'Cartão de crédito Mais';					break;
		case 118:	$method = 'Cartão de crédito Avista';				break;
		case 119:	$method = 'Cartão de crédito GRANDCARD';			break;
		case 120:	$method = 'Cartão de crédito Sorocred';				break;
		case 122:	$method = 'Cartão de crédito Up Policard';			break;
		case 123:	$method = 'Cartão de crédito Banese Card';			break;
		case 201:	$method = 'Boleto Bradesco';						break;
		case 202:	$method = 'Boleto Santander';						break;
		case 301:	$method = 'Débito online Bradesco';					break;
		case 302:	$method = 'Débito online Itaú';						break;
		case 303:	$method = 'Débito online Unibanco';					break;
		case 304:	$method = 'Débito online Banco do Brasil';			break;
		case 305:	$method = 'Débito online Banco Real';				break;
		case 306:	$method = 'Débito online Banrisul';					break;
		case 307:	$method = 'Débito online HSBC';						break;
		case 401:	$method = 'Saldo PagSeguro';						break;
		case 402:	$method = 'PIX';									break;
		case 501:	$method = 'Oi Paggo';								break;
		case 701:	$method = 'Depósito em conta - Banco do Brasil';	break;
	}

	return $method;
}

function ps_paymentStatus($id) {
	$status = $id;
	switch ($id) {
		case 1:	$status = 'Aguardando pagamento';	break;
		case 2:	$status = 'Em análise';				break;
		case 3:	$status = 'Paga';					break;
		case 4:	$status = 'Disponível';				break;
		case 5:	$status = 'Em disputa';				break;
		case 6:	$status = 'Devolvida';				break;
		case 7:	$status = 'Cancelada';				break;
		case 8:	$status = 'Debitado';				break;
		case 9:	$status = 'Retenção temporária';	break;
	}

	return $status;
}
