<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CompanySetting::count() > 0) {
            return;
        }

        Cache::forget('company_settings');

        CompanySetting::create([
            'name' => '',
            'legal_name' => 'Geckommercce S.A.C.',
            'ruc' => '12345678901',
            'slogan' => 'Tu ecommerce inteligente',
            'email' => 'contacto@geckomercce.com',
            'support_email' => 'soporte@geckomercce.com',
            'phone' => '+51 999 888 777',
            'support_phone' => '+51 977 888 111',
            'address' => 'Av. Principal 123, Lima, Perú',
            'website' => 'https://www.geckomercce.com',
            'social_links' => [
                'facebook' => 'https://facebook.com/geckomercce',
                'instagram' => 'https://instagram.com/geckomercce',
                'twitter' => 'https://twitter.com/geckomercce',
                'youtube' => 'https://www.youtube.com/@geckomercce',
                'tiktok' => 'https://www.tiktok.com/@geckomercce',
                'linkedin' => 'https://www.linkedin.com/company/geckomercce',
            ],
            'facebook_url' => 'https://facebook.com/geckomercce',
            'facebook_enabled' => true,
            'instagram_url' => 'https://instagram.com/geckomercce',
            'instagram_enabled' => true,
            'twitter_url' => 'https://twitter.com/geckomercce',
            'twitter_enabled' => true,
            'youtube_url' => 'https://www.youtube.com/@geckomercce',
            'youtube_enabled' => true,
            'tiktok_url' => 'https://www.tiktok.com/@geckomercce',
            'tiktok_enabled' => true,
            'linkedin_url' => 'https://www.linkedin.com/company/geckomercce',
            'linkedin_enabled' => true,
            'about' => 'Somos una tienda en línea enfocada en brindar la mejor experiencia de compra, combinando tecnología, logística y atención al cliente para que comprar sea rápido, simple y seguro.',
            'terms_conditions' => '<h2>1. Aceptación de los Términos</h2>
                <p>Al acceder y utilizar la plataforma de GeckoMerce, declaras que has leído, comprendido y aceptas los presentes Términos y Condiciones. Si no estás de acuerdo con alguna de las disposiciones aquí señaladas, te recomendamos no utilizar nuestros servicios.</p>

                <h2>2. Definiciones</h2>
                <p><strong>Usuario:</strong> Persona natural que navega, se registra o realiza compras a través del sitio web.<br>
                <strong>Plataforma:</strong> Sitio web y demás canales digitales operados por GeckoMerce para la exhibición y venta de productos.<br>
                <strong>Proveedor:</strong> Empresa o aliado comercial que ofrece productos a través de la plataforma.</p>

                <h2>3. Uso de la Plataforma</h2>
                <p>El uso de la plataforma está dirigido a personas mayores de edad con capacidad legal para contratar. El usuario se compromete a proporcionar información veraz, actualizada y completa en los formularios de registro, compra y contacto.</p>

                <h2>4. Precios, Ofertas y Disponibilidad</h2>
                <p>Los precios publicados incluyen los impuestos aplicables, salvo indicación contraria. Las ofertas y promociones tienen una vigencia determinada y están sujetas a stock. En caso de errores evidentes de precio o disponibilidad, GeckoMerce se reserva el derecho de anular la operación, informando oportunamente al usuario.</p>

                <h2>5. Envíos y Entregas</h2>
                <p>Los plazos de entrega son estimados y pueden variar según la dirección de destino, la disponibilidad del producto y eventos externos. El usuario será informado de los costos de envío y de las políticas de entrega durante el proceso de compra.</p>

                <h2>6. Cambios, Devoluciones y Garantías</h2>
                <p>Los productos cuentan con garantías legales y/o comerciales según la normativa vigente. Los procedimientos y plazos para solicitar cambios, devoluciones o hacer efectiva una garantía se detallan en nuestras políticas de envíos y devoluciones.</p>

                <h2>7. Responsabilidad del Usuario</h2>
                <p>El usuario se compromete a utilizar la plataforma de forma responsable, absteniéndose de realizar actividades fraudulentas, introducir software malicioso o vulnerar la seguridad del sistema.</p>

                <h2>8. Modificaciones de los Términos</h2>
                <p>GeckoMerce podrá actualizar estos Términos y Condiciones cuando sea necesario. La versión vigente estará siempre disponible en esta página, por lo que recomendamos revisarla periódicamente.</p>',
                            'privacy_policy' => '<h2>1. Alcance de la Política</h2>
                <p>La presente Política de Privacidad describe cómo GeckoMerce recopila, utiliza, almacena y protege la información personal de los usuarios que interactúan con nuestra plataforma.</p>

                <h2>2. Datos que Podemos Recopilar</h2>
                <p>Podemos recopilar datos de identificación (nombre, documento de identidad), datos de contacto (correo electrónico, teléfono, dirección), datos de navegación (páginas visitadas, dispositivo, navegador) y datos relacionados con tus compras (productos adquiridos, historial de pedidos, medios de pago utilizados).</p>

                <h2>3. Finalidades del Tratamiento</h2>
                <p>Utilizamos tus datos para: procesar y entregar tus pedidos, gestionar tu cuenta de usuario, enviar comunicaciones relacionadas con tus compras, mejorar la experiencia de uso de la plataforma y, cuando lo autorices, enviarte ofertas y contenidos promocionales personalizados.</p>

                <h2>4. Bases Legales del Tratamiento</h2>
                <p>Tratamos tus datos en base a el cumplimiento de obligaciones contractuales, el cumplimiento de obligaciones legales, el interés legítimo de mejorar nuestros servicios y, cuando corresponda, tu consentimiento expreso.</p>

                <h2>5. Conservación y Seguridad</h2>
                <p>Conservamos tus datos personales solo durante el tiempo necesario para cumplir las finalidades descritas y las exigencias legales aplicables. Implementamos medidas de seguridad técnicas y organizativas para proteger tu información frente a accesos no autorizados, pérdida o alteración.</p>

                <h2>6. Derechos de los Usuarios</h2>
                <p>Puedes ejercer tus derechos de acceso, rectificación, actualización, cancelación y oposición, así como revocar tu consentimiento cuando corresponda, contactándonos a través de nuestros canales oficiales de soporte.</p>

                <h2>7. Transferencias y Encargados</h2>
                <p>En algunos casos, podremos compartir tu información con proveedores de servicios (logística, medios de pago, soporte) que actúan como encargados de tratamiento y están obligados a cumplir con esta política y con las normas aplicables de protección de datos.</p>',
                            'claims_book_information' => '<h2>1. Libro de Reclamaciones</h2>
                <p>En cumplimiento de la normativa aplicable en materia de protección al consumidor, GeckoMerce pone a disposición de sus usuarios un Libro de Reclamaciones virtual para registrar reclamos y quejas relacionados con los productos o servicios ofrecidos a través de la plataforma.</p>

                <h2>2. Definiciones</h2>
                <p><strong>Reclamo:</strong> Disconformidad relacionada directamente con los productos o servicios adquiridos (por ejemplo, problemas de calidad, incumplimiento de plazos, errores en la facturación).<br>
                <strong>Queja:</strong> Malestar o descontento que no se vincula directamente con los productos o servicios (por ejemplo, mala atención del personal, tiempos de respuesta insatisfactorios).</p>

                <h2>3. Procedimiento para Registrar un Reclamo o Queja</h2>
                <p>El usuario puede registrar su reclamo o queja a través del formulario disponible en la sección "Libro de Reclamaciones" de este sitio. Deberá proporcionar sus datos de contacto, seleccionar el tipo de registro (reclamo o queja) y describir de forma clara los hechos ocurridos.</p>

                <h2>4. Plazos de Atención</h2>
                <p>Una vez registrado el reclamo o queja, nuestro equipo de atención al cliente evaluará el caso y brindará una respuesta dentro de los plazos establecidos por la normativa vigente, comunicándose a través de los medios de contacto proporcionados por el usuario.</p>

                <h2>5. Seguimiento</h2>
                <p>El usuario podrá solicitar información sobre el estado de su reclamo o queja utilizando nuestros canales de soporte, indicando el número de registro o la fecha aproximada en la que fue presentado.</p>',
        ]);
    }
}
