use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceLogsTable extends Migration
{
    public function up()
    {
        Schema::create('device_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->boolean('door_locked')->default(true);
            $table->boolean('motion')->default(false);
            $table->boolean('fire')->default(false);
            $table->json('ambient')->nullable(); // store ambient sensor readings (e.g., light, air quality)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_logs');
    }
}
