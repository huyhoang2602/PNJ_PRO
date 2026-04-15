<?php
// Cần config.php và startup.php của OpenCart
// Nhưng cách nhanh nhất là dùng controller hiện có.
// Tôi sẽ sửa tạm DcMinimal::beforeHeader để log các sự kiện đang có.

namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class Debug extends \Opencart\System\Engine\Controller {
    public function checkEvents(): void {
        $this->load->model('setting/event');
        $events = $this->model_setting_event->getEvents();
        
        echo "<h1>Active Events</h1><pre>";
        foreach ($events as $v) {
            echo "Code: " . $v['code'] . " | Trigger: " . $v['trigger'] . " | Action: " . $v['action'] . " | Status: " . $v['status'] . "\n";
        }
        echo "</pre>";
    }
}
