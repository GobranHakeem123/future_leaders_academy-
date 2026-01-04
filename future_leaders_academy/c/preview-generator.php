<?php
// preview-generator.php
require_once 'config.php';

function generatePDFPreview($title, $author = '') {
    ob_start();
    ?>
    <div class="pdf-preview">
        <div class="pdf-preview-content">
            <div class="pdf-preview-header">
                <div class="pdf-preview-dot"></div>
                <div class="pdf-preview-dot"></div>
                <div class="pdf-preview-dot"></div>
            </div>
            <div class="pdf-preview-body">
                <div class="pdf-preview-line" style="width: 80%; height: 20px; background: #FF4757; margin-bottom: 15px;"></div>
                <div class="pdf-preview-line" style="width: 60%; height: 12px; margin-bottom: 10px;"></div>
                <div class="pdf-preview-line" style="width: 70%; height: 8px; margin-bottom: 20px;"></div>
                
                <div class="pdf-preview-line"></div>
                <div class="pdf-preview-line short"></div>
                <div class="pdf-preview-line" style="width: 90%;"></div>
                <div class="pdf-preview-line"></div>
                <div class="pdf-preview-line short"></div>
                
                <div style="flex: 1;"></div>
                
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: #f0f0f0;"></div>
                        <div>
                            <div style="width: 60px; height: 8px; background: #f0f0f0; border-radius: 4px; margin-bottom: 4px;"></div>
                            <div style="width: 40px; height: 6px; background: #f0f0f0; border-radius: 3px;"></div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="width: 50px; height: 8px; background: #f0f0f0; border-radius: 4px; margin-bottom: 4px;"></div>
                        <div style="width: 30px; height: 6px; background: #f0f0f0; border-radius: 3px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pdf-watermark">PDF</div>
    </div>
    <?php
    return ob_get_clean();
}

function generatePPTPreview($title) {
    ob_start();
    ?>
    <div class="ppt-preview">
        <div class="ppt-preview-slide">
            <div class="ppt-preview-title-bar"><?= substr($title, 0, 30) . (strlen($title) > 30 ? '...' : '') ?></div>
            <div class="ppt-preview-content">
                <div class="ppt-preview-bullet">
                    <div class="ppt-preview-bullet-dot"></div>
                    <div class="ppt-preview-text" style="width: 80%;"></div>
                </div>
                <div class="ppt-preview-bullet">
                    <div class="ppt-preview-bullet-dot"></div>
                    <div class="ppt-preview-text" style="width: 70%;"></div>
                </div>
                <div class="ppt-preview-bullet">
                    <div class="ppt-preview-bullet-dot"></div>
                    <div class="ppt-preview-text" style="width: 85%;"></div>
                </div>
                <div class="ppt-preview-bullet">
                    <div class="ppt-preview-bullet-dot"></div>
                    <div class="ppt-preview-text" style="width: 60%;"></div>
                </div>
                
                <div style="flex: 1;"></div>
                
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 20px;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #EC4899;"></div>
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: rgba(236, 72, 153, 0.5);"></div>
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: rgba(236, 72, 153, 0.3);"></div>
                </div>
            </div>
        </div>
        <div class="ppt-watermark">PPT</div>
    </div>
    <?php
    return ob_get_clean();
}

function generateWordPreview($title) {
    ob_start();
    ?>
    <div class="doc-preview">
        <div class="doc-preview-page">
            <div class="doc-preview-line title"></div>
            <div class="doc-preview-line subtitle"></div>
            <div style="height: 20px;"></div>
            
            <div class="doc-preview-line" style="width: 100%;"></div>
            <div class="doc-preview-line" style="width: 95%;"></div>
            <div class="doc-preview-line" style="width: 98%;"></div>
            <div class="doc-preview-line" style="width: 85%;"></div>
            <div class="doc-preview-line" style="width: 90%; margin-bottom: 20px;"></div>
            
            <div class="doc-preview-line" style="width: 100%; height: 12px;"></div>
            <div class="doc-preview-line" style="width: 100%; height: 12px;"></div>
            <div class="doc-preview-line" style="width: 80%; height: 12px; margin-bottom: 20px;"></div>
            
            <div style="display: flex; gap: 10px; margin-top: auto;">
                <div style="width: 60px; height: 8px; background: #e5e5e5; border-radius: 2px;"></div>
                <div style="width: 40px; height: 8px; background: #e5e5e5; border-radius: 2px;"></div>
                <div style="width: 50px; height: 8px; background: #3B82F6; border-radius: 2px; margin-left: auto;"></div>
            </div>
        </div>
        <div class="doc-watermark">DOC</div>
    </div>
    <?php
    return ob_get_clean();
}

function generateExcelPreview() {
    ob_start();
    ?>
    <div class="excel-preview">
        <div class="excel-preview-grid">
            <?php
            $headers = ['A', 'B', 'C', 'D'];
            $data = [
                ['Item 1', '100', '10%', '$1,000'],
                ['Item 2', '200', '15%', '$3,000'],
                ['Item 3', '150', '12%', '$1,800'],
                ['Item 4', '300', '20%', '$6,000']
            ];
            
            foreach ($headers as $header): ?>
                <div class="excel-preview-cell header"><?= $header ?></div>
            <?php endforeach; ?>
            
            <?php foreach ($data as $row): ?>
                <?php foreach ($row as $cell): ?>
                    <div class="excel-preview-cell"><?= $cell ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <div class="excel-watermark">XLS</div>
    </div>
    <?php
    return ob_get_clean();
}

function generateArchivePreview($files = 4) {
    ob_start();
    ?>
    <div class="archive-preview">
        <div class="archive-preview-files">
            <?php 
            $fileNames = [
                'document.pdf',
                'presentation.pptx',
                'data.xlsx',
                'notes.docx',
                'images.zip'
            ];
            
            $icons = ['fa-file-pdf', 'fa-file-powerpoint', 'fa-file-excel', 'fa-file-word', 'fa-file-archive'];
            $colors = ['#FF4757', '#EC4899', '#10B981', '#3B82F6', '#F59E0B'];
            
            for ($i = 0; $i < min($files, 5); $i++): ?>
                <div class="archive-preview-file">
                    <i class="fas <?= $icons[$i] ?> archive-preview-file-icon" 
                       style="color: <?= $colors[$i] ?>"></i>
                    <div class="archive-preview-file-name" style="width: <?= rand(60, 90) ?>%"></div>
                </div>
            <?php endfor; ?>
        </div>
        <div class="archive-watermark">ZIP</div>
    </div>
    <?php
    return ob_get_clean();
}