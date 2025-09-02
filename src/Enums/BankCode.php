<?php

namespace MohamedSaid\PaymobPayout\Enums;

enum BankCode: string
{
    case AUB = 'AUB';
    case CIB = 'CIB';
    case NBE = 'NBE';
    case MISR = 'MISR';
    case ALEX = 'ALEX';
    case CAE = 'CAE';
    case ADIB = 'ADIB';
    case ARAB = 'ARAB';
    case QNB = 'QNB';
    case HSBC = 'HSBC';
    case SCB = 'SCB';
    case AAIB = 'AAIB';
    case BLOM = 'BLOM';
    case ARIB = 'ARIB';
    case EDBE = 'EDBE';
    case PDAC = 'PDAC';
    case UBOE = 'UBOE';
    case SAIB = 'SAIB';
    case ADCB = 'ADCB';
    case NSGB = 'NSGB';
    case EGBE = 'EGBE';
    case EXDE = 'EXDE';
    case CRED = 'CRED';
    case ENBD = 'ENBD';
    case MASH = 'MASH';
    case FIBE = 'FIBE';
    case ATQB = 'ATQB';
    case ALAH = 'ALAH';
    case MIDG = 'MIDG';
    case CIEB = 'CIEB';
    case IBANK = 'IBANK';

    public function getLabel(): string
    {
        return match ($this) {
            self::AUB => __('Ahli United Bank'),
            self::CIB => __('Commercial International Bank'),
            self::NBE => __('National Bank of Egypt'),
            self::MISR => __('Banque Misr'),
            self::ALEX => __('Bank of Alexandria'),
            self::CAE => __('Credit Agricole Egypt'),
            self::ADIB => __('Abu Dhabi Islamic Bank'),
            self::ARAB => __('Arab African International Bank'),
            self::QNB => __('QNB ALAHLI'),
            self::HSBC => __('HSBC Bank Egypt'),
            self::SCB => __('Suez Canal Bank'),
            self::AAIB => __('Arab African International Bank'),
            self::BLOM => __('Blom Bank Egypt'),
            self::ARIB => __('Arab Investment Bank'),
            self::EDBE => __('Export Development Bank of Egypt'),
            self::PDAC => __('Principal Bank for Development and Agricultural Credit'),
            self::UBOE => __('Union Bank of Egypt'),
            self::SAIB => __('Société Arabe Internationale de Banque'),
            self::ADCB => __('Abu Dhabi Commercial Bank'),
            self::NSGB => __('Nasser Social Bank'),
            self::EGBE => __('Egyptian Gulf Bank'),
            self::EXDE => __('Export Development Bank'),
            self::CRED => __('Credit Agricole Egypt'),
            self::ENBD => __('Emirates NBD Egypt'),
            self::MASH => __('Mashreq Bank'),
            self::FIBE => __('Faisal Islamic Bank of Egypt'),
            self::ATQB => __('Al Ahly Bank of Kuwait'),
            self::ALAH => __('Al Ahly Bank'),
            self::MIDG => __('Midroc Gold Bank'),
            self::CIEB => __('Crédit Industriel et Commercial'),
            self::IBANK => __('Investment Bank'),
        };
    }

    public static function all(array $except = []): array
    {
        $cases = self::cases();

        if (empty($except)) {
            return $cases;
        }

        return array_filter($cases, fn ($case): bool => ! in_array($case, $except));
    }
}
