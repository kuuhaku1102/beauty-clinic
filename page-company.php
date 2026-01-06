<?php
/**
 * Template Name: 運営者情報
 * E-E-A-T強化
 */
get_header();
?>

<div class="bd-static-page">
    <div class="bd-section-container">
        <div class="bd-breadcrumb">
            <a href="<?php echo home_url(); ?>">ホーム</a>
            <span class="bd-breadcrumb-separator">›</span>
            <span>運営者情報</span>
        </div>
        
        <h1 class="bd-static-title">運営者情報</h1>
        
        <div class="bd-static-content">
            <table class="bd-company-table">
                <tbody>
                    <tr>
                        <th>サイト名</th>
                        <td>美容クリニックラボ</td>
                    </tr>
                    <tr>
                        <th>運営会社</th>
                        <td>株式会社ビューティーテック</td>
                    </tr>
                    <tr>
                        <th>所在地</th>
                        <td>〒150-0002 東京都渋谷区渋谷2-24-12</td>
                    </tr>
                    <tr>
                        <th>代表者</th>
                        <td>山田 花子</td>
                    </tr>
                    <tr>
                        <th>設立</th>
                        <td>2023年4月1日</td>
                    </tr>
                    <tr>
                        <th>事業内容</th>
                        <td>
                            <ul>
                                <li>美容医療に関する情報提供メディアの運営</li>
                                <li>Webサイトの企画・制作・コンサルティング</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th>連絡先</th>
                        <td>contact@beauty-clinic-lab.com</td>
                    </tr>
                    <tr>
                        <th>サイトURL</th>
                        <td><a href="<?php echo home_url(); ?>"><?php echo home_url(); ?></a></td>
                    </tr>
                </tbody>
            </table>
            
            <h2 class="bd-section-subtitle">サイト運営方針</h2>
            <p>美容クリニックラボは、美容医療に関する正確で信頼できる情報を提供することを目指しています。専門家による監修のもと、読者の皆様が安心してクリニック選びを行えるよう、中立的な立場から情報発信に努めてまいります。</p>
            
            <h2 class="bd-section-subtitle">免責事項</h2>
            <p>当サイトの情報は、一般的な情報提供を目的としており、医学的なアドバイスを提供するものではありません。施術に関する最終的な判断は、ご自身の責任において、必ず専門の医療機関にご相談ください。当サイトの情報を利用した結果生じた損害について、当サイトは一切の責任を負いかねます。</p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
