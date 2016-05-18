<?php
$account        = isset( $_GET['receive_payment'] ) && $_GET['receive_payment'] == 'true' ? true : false;
$account_id     = $account && isset( $_GET['bank'] ) ? intval( $_GET['bank'] ) : false;
$customer_class = $account_id ? 'erp-ac-payment-receive' : '';
$transaction_id = isset( $_GET['transaction_id'] ) ? intval( $_GET['transaction_id'] ) : false;

$transaction    = [];
$jor_itms       = [];
$main_ledger_id = '';

if ( $transaction_id ) {
    $transaction = erp_ac_get_all_transaction([
        'id'     => $transaction_id,
        'status' => 'draft',
        'join'   => ['journals', 'items'],
        'type'   => ['sales'],
        'output_by' => 'array'
    ]);

    $transaction = reset( $transaction );

    foreach ( $transaction['journals'] as $key => $journal ) {

        $journal_id = $journal['id'];

        if ( $journal['type'] == 'main' ) {
            $main_ledger_id  = $journal['ledger_id'];
            $jor_itms['main'] = $journal;

        } else {
            $jor_itms['journal'][] = $journal;
        }
    }

    foreach ( $transaction['items'] as $key => $item ) {
        $journal_id = $item['journal_id'];
        $jor_itms['item'][] = $item;

    }
}

$items_for_tax = isset( $transaction['items'] ) ? $transaction['items'] : [];
$tax_labels = erp_ac_get_trans_unit_tax_rate( $items_for_tax );

$main_ledger_id = isset( $_GET['bank'] ) ? intval( $_GET['bank'] ) : $main_ledger_id;

?>
<div class="wrap erp-ac-form-wrap">

    <h2><?php _e( 'Receive Payment', '$domain' ); ?></h2>

    <?php
    $dropdown = erp_ac_get_chart_dropdown([
        'exclude'  => [1, 2, 3],

    ] );

    $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
        'name'     => 'line_account[]',
        'selected' => isset( $journal['ledger_id'] ) ? $journal['ledger_id'] : false,
        'class'    => 'select2'
    ) );

    ?>

    <form action="" method="post" class="erp-form" style="margin-top: 30px;">

        <ul class="form-fields block" style="width:50%;">

            <li>
                <ul class="erp-form-fields two-col block">
                    <li class="erp-form-field erp-ac-replace-wrap">
                        <div class="erp-ac-replace-content">
                            <?php

                            erp_html_form_input( array(
                                'label'       => __( 'Customer', 'accounting' ),
                                'name'        => 'user_id',
                                'placeholder' => __( 'Select a payee', 'accounting' ),
                                'value'       => isset( $transaction['user_id'] ) ? $transaction['user_id'] : '',
                                'type'        => 'select',
                                'class'       => $transaction_id ? 'select2 erp-ac-not-found-in-drop' : 'select2 erp-ac-payment-receive erp-ac-not-found-in-drop',
                                'options'     => [ '' => __( '&mdash; Select &mdash;', 'accounting' ) ] + erp_get_peoples_array( ['type' => 'customer', 'number' => 100 ] ),
                                'custom_attr' => [
                                    'data-content' => 'erp-ac-new-customer-content-pop',
                                ],
                            ) );
                            ?>
                            <div><a href="#" data-content="erp-ac-new-customer-content-pop" class="erp-ac-not-found-btn-in-drop erp-ac-more-customer"><?php _e( 'Create New', 'accounting' ); ?></a></div>
                        </div>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Reference', 'accounting' ),
                            'name'  => 'ref',
                            'type'  => 'text',
                            'class' => 'erp-ac-reference-field',
                            'addon' => '#',
                            'value' => isset( $transaction['ref'] ) ? $transaction['ref'] : ''
                        ) );
                        ?>
                    </li>
                </ul>
            </li>

            <li>
                <ul class="erp-form-fields two-col block clearfix">
                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'       => __( 'Payment Date', 'accounting' ),
                            'name'        => 'issue_date',
                            'placeholder' => date( 'Y-m-d' ),
                            'type'        => 'text',
                            'required'    => true,
                            'class'       => 'erp-date-field',
                            'value'       => isset( $transaction['issue_date'] ) ? $transaction['issue_date'] : ''
                        ) );
                        ?>
                    </li>

                    <li class="cols erp-form-field">
                        <?php
                            erp_html_form_input( array(
                                'label'       => __( 'Deposit To', 'accounting' ),
                                'name'        => 'account_id',
                                'placeholder' => __( 'Select an Account', 'accounting' ),
                                'type'        => 'select',
                                'class'       => 'select2 erp-ac-deposit-dropdown',
                                'value'       => $main_ledger_id,
                                'required'    => true,
                                'options'     => [ '' => __( '&mdash; Select &mdash;', 'accounting' ) ] + erp_ac_get_bank_dropdown()
                            ) );
                        ?>

                    </li>
                </ul>
            </li>

        </ul>


        <div class="erp-ac-receive-payment-table">
            <?php include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php';?>
        </div>

        <?php include dirname( dirname( __FILE__ ) ) . '/common/memo.php'; ?>

        <input type="hidden" name="field_id" value="0">
        <input type="hidden" name="status" value="closed">
        <input type="hidden" name="type" value="sales">
        <input type="hidden" name="form_type" value="payment">
        <input type="hidden" name="page" value="erp-accounting-sales">
        <input type="hidden" name="erp-action" value="ac-new-sales-payment">
        <?php
            erp_html_form_input( array(
                'name'        => 'id',
                'type'        => 'hidden',
                'value'       => $transaction_id
            ) );
        ?>

        <?php wp_nonce_field( 'erp-ac-trans-new' ); ?>
        <input type="submit" name="submit_erp_ac_trans" id="submit_erp_ac_trans" class="button button-primary" value="<?php _e( 'Receive Payment', 'accounting'); ?>">
        <input type="submit" name="submit_erp_ac_trans_draft" id="submit_erp_ac_trans_draft" class="button button-secondary" value="<?php _e( 'Save as Draft', 'accounting' ); ?>">
    </form>
    <div class="erp-ac-receive-payment-table-clone" style="display: none;">

        <?php 
        $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
            'name'     => 'line_account[]',
            'class'    => 'erp-ac-selece-custom'
        ) );

        $jor_itms = [];
        include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php';?>
    </div>

</div>