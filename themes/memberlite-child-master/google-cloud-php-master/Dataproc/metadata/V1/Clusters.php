<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dataproc/v1/clusters.proto

namespace GPBMetadata\Google\Cloud\Dataproc\V1;

class Clusters
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Annotations::initOnce();
        \GPBMetadata\Google\Api\Client::initOnce();
        \GPBMetadata\Google\Api\FieldBehavior::initOnce();
        \GPBMetadata\Google\Cloud\Dataproc\V1\Shared::initOnce();
        \GPBMetadata\Google\Longrunning\Operations::initOnce();
        \GPBMetadata\Google\Protobuf\Duration::initOnce();
        \GPBMetadata\Google\Protobuf\FieldMask::initOnce();
        \GPBMetadata\Google\Protobuf\Timestamp::initOnce();
        $pool->internalAddGeneratedFile(hex2bin(
            "0a973d0a27676f6f676c652f636c6f75642f6461746170726f632f76312f" .
            "636c7573746572732e70726f746f1218676f6f676c652e636c6f75642e64" .
            "61746170726f632e76311a17676f6f676c652f6170692f636c69656e742e" .
            "70726f746f1a1f676f6f676c652f6170692f6669656c645f626568617669" .
            "6f722e70726f746f1a25676f6f676c652f636c6f75642f6461746170726f" .
            "632f76312f7368617265642e70726f746f1a23676f6f676c652f6c6f6e67" .
            "72756e6e696e672f6f7065726174696f6e732e70726f746f1a1e676f6f67" .
            "6c652f70726f746f6275662f6475726174696f6e2e70726f746f1a20676f" .
            "6f676c652f70726f746f6275662f6669656c645f6d61736b2e70726f746f" .
            "1a1f676f6f676c652f70726f746f6275662f74696d657374616d702e7072" .
            "6f746f22c8030a07436c757374657212170a0a70726f6a6563745f696418" .
            "01200128094203e0410212190a0c636c75737465725f6e616d6518022001" .
            "28094203e04102123c0a06636f6e66696718032001280b32272e676f6f67" .
            "6c652e636c6f75642e6461746170726f632e76312e436c7573746572436f" .
            "6e6669674203e0410212420a066c6162656c7318082003280b322d2e676f" .
            "6f676c652e636c6f75642e6461746170726f632e76312e436c7573746572" .
            "2e4c6162656c73456e7472794203e04101123c0a06737461747573180420" .
            "01280b32272e676f6f676c652e636c6f75642e6461746170726f632e7631" .
            "2e436c75737465725374617475734203e0410312440a0e7374617475735f" .
            "686973746f727918072003280b32272e676f6f676c652e636c6f75642e64" .
            "61746170726f632e76312e436c75737465725374617475734203e0410312" .
            "190a0c636c75737465725f757569641806200128094203e0410312390a07" .
            "6d65747269637318092001280b32282e676f6f676c652e636c6f75642e64" .
            "61746170726f632e76312e436c75737465724d6574726963731a2d0a0b4c" .
            "6162656c73456e747279120b0a036b6579180120012809120d0a0576616c" .
            "75651802200128093a02380122b0060a0d436c7573746572436f6e666967" .
            "121a0a0d636f6e6669675f6275636b65741801200128094203e04101124b" .
            "0a126763655f636c75737465725f636f6e66696718082001280b322a2e67" .
            "6f6f676c652e636c6f75642e6461746170726f632e76312e476365436c75" .
            "73746572436f6e6669674203e0410112490a0d6d61737465725f636f6e66" .
            "696718092001280b322d2e676f6f676c652e636c6f75642e646174617072" .
            "6f632e76312e496e7374616e636547726f7570436f6e6669674203e04101" .
            "12490a0d776f726b65725f636f6e666967180a2001280b322d2e676f6f67" .
            "6c652e636c6f75642e6461746170726f632e76312e496e7374616e636547" .
            "726f7570436f6e6669674203e0410112530a177365636f6e646172795f77" .
            "6f726b65725f636f6e666967180c2001280b322d2e676f6f676c652e636c" .
            "6f75642e6461746170726f632e76312e496e7374616e636547726f757043" .
            "6f6e6669674203e0410112460a0f736f6674776172655f636f6e66696718" .
            "0d2001280b32282e676f6f676c652e636c6f75642e6461746170726f632e" .
            "76312e536f667477617265436f6e6669674203e0410112570a16696e6974" .
            "69616c697a6174696f6e5f616374696f6e73180b2003280b32322e676f6f" .
            "676c652e636c6f75642e6461746170726f632e76312e4e6f6465496e6974" .
            "69616c697a6174696f6e416374696f6e4203e04101124a0a11656e637279" .
            "7074696f6e5f636f6e666967180f2001280b322a2e676f6f676c652e636c" .
            "6f75642e6461746170726f632e76312e456e6372797074696f6e436f6e66" .
            "69674203e04101124c0a126175746f7363616c696e675f636f6e66696718" .
            "122001280b322b2e676f6f676c652e636c6f75642e6461746170726f632e" .
            "76312e4175746f7363616c696e67436f6e6669674203e0410112460a0f73" .
            "656375726974795f636f6e66696718102001280b32282e676f6f676c652e" .
            "636c6f75642e6461746170726f632e76312e5365637572697479436f6e66" .
            "69674203e0410112480a106c6966656379636c655f636f6e666967181120" .
            "01280b32292e676f6f676c652e636c6f75642e6461746170726f632e7631" .
            "2e4c6966656379636c65436f6e6669674203e04101222c0a114175746f73" .
            "63616c696e67436f6e66696712170a0a706f6c6963795f75726918012001" .
            "28094203e0410122340a10456e6372797074696f6e436f6e66696712200a" .
            "136763655f70645f6b6d735f6b65795f6e616d651801200128094203e041" .
            "01229f030a10476365436c7573746572436f6e66696712150a087a6f6e65" .
            "5f7572691801200128094203e0410112180a0b6e6574776f726b5f757269" .
            "1802200128094203e04101121b0a0e7375626e6574776f726b5f75726918" .
            "06200128094203e04101121d0a10696e7465726e616c5f69705f6f6e6c79" .
            "1807200128084203e04101121c0a0f736572766963655f6163636f756e74" .
            "1808200128094203e0410112230a16736572766963655f6163636f756e74" .
            "5f73636f7065731803200328094203e04101120c0a047461677318042003" .
            "2809124a0a086d6574616461746118052003280b32382e676f6f676c652e" .
            "636c6f75642e6461746170726f632e76312e476365436c7573746572436f" .
            "6e6669672e4d65746164617461456e74727912500a147265736572766174" .
            "696f6e5f616666696e697479180b2001280b322d2e676f6f676c652e636c" .
            "6f75642e6461746170726f632e76312e5265736572766174696f6e416666" .
            "696e6974794203e041011a2f0a0d4d65746164617461456e747279120b0a" .
            "036b6579180120012809120d0a0576616c75651802200128093a02380122" .
            "9a030a13496e7374616e636547726f7570436f6e666967121a0a0d6e756d" .
            "5f696e7374616e6365731801200128054203e04101121b0a0e696e737461" .
            "6e63655f6e616d65731802200328094203e0410312160a09696d6167655f" .
            "7572691803200128094203e04101121d0a106d616368696e655f74797065" .
            "5f7572691804200128094203e04101123e0a0b6469736b5f636f6e666967" .
            "18052001280b32242e676f6f676c652e636c6f75642e6461746170726f63" .
            "2e76312e4469736b436f6e6669674203e04101121b0a0e69735f70726565" .
            "6d707469626c651806200128084203e04101124f0a146d616e616765645f" .
            "67726f75705f636f6e66696718072001280b322c2e676f6f676c652e636c" .
            "6f75642e6461746170726f632e76312e4d616e6167656447726f7570436f" .
            "6e6669674203e0410312460a0c616363656c657261746f72731808200328" .
            "0b322b2e676f6f676c652e636c6f75642e6461746170726f632e76312e41" .
            "6363656c657261746f72436f6e6669674203e04101121d0a106d696e5f63" .
            "70755f706c6174666f726d1809200128094203e0410122630a124d616e61" .
            "67656447726f7570436f6e66696712230a16696e7374616e63655f74656d" .
            "706c6174655f6e616d651801200128094203e0410312280a1b696e737461" .
            "6e63655f67726f75705f6d616e616765725f6e616d651802200128094203" .
            "e04103224c0a11416363656c657261746f72436f6e666967121c0a146163" .
            "63656c657261746f725f747970655f75726918012001280912190a116163" .
            "63656c657261746f725f636f756e7418022001280522660a0a4469736b43" .
            "6f6e666967121b0a0e626f6f745f6469736b5f7479706518032001280942" .
            "03e04101121e0a11626f6f745f6469736b5f73697a655f67621801200128" .
            "054203e04101121b0a0e6e756d5f6c6f63616c5f73736473180220012805" .
            "4203e0410122730a184e6f6465496e697469616c697a6174696f6e416374" .
            "696f6e121c0a0f65786563757461626c655f66696c651801200128094203" .
            "e0410212390a11657865637574696f6e5f74696d656f757418022001280b" .
            "32192e676f6f676c652e70726f746f6275662e4475726174696f6e4203e0" .
            "41012284030a0d436c757374657253746174757312410a05737461746518" .
            "012001280e322d2e676f6f676c652e636c6f75642e6461746170726f632e" .
            "76312e436c75737465725374617475732e53746174654203e0410312160a" .
            "0664657461696c1802200128094206e04103e0410112390a107374617465" .
            "5f73746172745f74696d6518032001280b321a2e676f6f676c652e70726f" .
            "746f6275662e54696d657374616d704203e0410312470a08737562737461" .
            "746518042001280e32302e676f6f676c652e636c6f75642e646174617072" .
            "6f632e76312e436c75737465725374617475732e53756273746174654203" .
            "e0410322560a055374617465120b0a07554e4b4e4f574e1000120c0a0843" .
            "52454154494e471001120b0a0752554e4e494e47100212090a054552524f" .
            "521003120c0a0844454c4554494e471004120c0a085550444154494e4710" .
            "05223c0a085375627374617465120f0a0b554e5350454349464945441000" .
            "120d0a09554e4845414c544859100112100a0c5354414c455f5354415455" .
            "53100222530a0e5365637572697479436f6e66696712410a0f6b65726265" .
            "726f735f636f6e66696718012001280b32282e676f6f676c652e636c6f75" .
            "642e6461746170726f632e76312e4b65726265726f73436f6e6669672290" .
            "040a0e4b65726265726f73436f6e666967121c0a0f656e61626c655f6b65" .
            "726265726f731801200128084203e0410112280a1b726f6f745f7072696e" .
            "636970616c5f70617373776f72645f7572691802200128094203e0410212" .
            "180a0b6b6d735f6b65795f7572691803200128094203e0410212190a0c6b" .
            "657973746f72655f7572691804200128094203e04101121b0a0e74727573" .
            "7473746f72655f7572691805200128094203e0410112220a156b65797374" .
            "6f72655f70617373776f72645f7572691806200128094203e04101121d0a" .
            "106b65795f70617373776f72645f7572691807200128094203e041011224" .
            "0a17747275737473746f72655f70617373776f72645f7572691808200128" .
            "094203e0410112240a1763726f73735f7265616c6d5f74727573745f7265" .
            "616c6d1809200128094203e0410112220a1563726f73735f7265616c6d5f" .
            "74727573745f6b6463180a200128094203e04101122b0a1e63726f73735f" .
            "7265616c6d5f74727573745f61646d696e5f736572766572180b20012809" .
            "4203e0410112320a2563726f73735f7265616c6d5f74727573745f736861" .
            "7265645f70617373776f72645f757269180c200128094203e04101121b0a" .
            "0e6b64635f64625f6b65795f757269180d200128094203e04101121f0a12" .
            "7467745f6c69666574696d655f686f757273180e200128054203e0410112" .
            "120a057265616c6d180f200128094203e0410122f9010a0e536f66747761" .
            "7265436f6e666967121a0a0d696d6167655f76657273696f6e1801200128" .
            "094203e0410112510a0a70726f7065727469657318022003280b32382e67" .
            "6f6f676c652e636c6f75642e6461746170726f632e76312e536f66747761" .
            "7265436f6e6669672e50726f70657274696573456e7472794203e0410112" .
            "450a136f7074696f6e616c5f636f6d706f6e656e747318032003280e3223" .
            "2e676f6f676c652e636c6f75642e6461746170726f632e76312e436f6d70" .
            "6f6e656e744203e041011a310a0f50726f70657274696573456e74727912" .
            "0b0a036b6579180120012809120d0a0576616c75651802200128093a0238" .
            "012283020a0f4c6966656379636c65436f6e66696712370a0f69646c655f" .
            "64656c6574655f74746c18012001280b32192e676f6f676c652e70726f74" .
            "6f6275662e4475726174696f6e4203e04101123b0a106175746f5f64656c" .
            "6574655f74696d6518022001280b321a2e676f6f676c652e70726f746f62" .
            "75662e54696d657374616d704203e04101480012390a0f6175746f5f6465" .
            "6c6574655f74746c18032001280b32192e676f6f676c652e70726f746f62" .
            "75662e4475726174696f6e4203e04101480012380a0f69646c655f737461" .
            "72745f74696d6518042001280b321a2e676f6f676c652e70726f746f6275" .
            "662e54696d657374616d704203e0410342050a0374746c229a020a0e436c" .
            "75737465724d657472696373124f0a0c686466735f6d6574726963731801" .
            "2003280b32392e676f6f676c652e636c6f75642e6461746170726f632e76" .
            "312e436c75737465724d6574726963732e486466734d657472696373456e" .
            "747279124f0a0c7961726e5f6d65747269637318022003280b32392e676f" .
            "6f676c652e636c6f75642e6461746170726f632e76312e436c7573746572" .
            "4d6574726963732e5961726e4d657472696373456e7472791a320a104864" .
            "66734d657472696373456e747279120b0a036b6579180120012809120d0a" .
            "0576616c75651802200128033a0238011a320a105961726e4d6574726963" .
            "73456e747279120b0a036b6579180120012809120d0a0576616c75651802" .
            "200128033a0238012296010a14437265617465436c757374657252657175" .
            "65737412170a0a70726f6a6563745f69641801200128094203e041021213" .
            "0a06726567696f6e1803200128094203e0410212370a07636c7573746572" .
            "18022001280b32212e676f6f676c652e636c6f75642e6461746170726f63" .
            "2e76312e436c75737465724203e0410212170a0a726571756573745f6964" .
            "1804200128094203e0410122ae020a14557064617465436c757374657252" .
            "65717565737412170a0a70726f6a6563745f69641801200128094203e041" .
            "0212130a06726567696f6e1805200128094203e0410212190a0c636c7573" .
            "7465725f6e616d651802200128094203e0410212370a07636c7573746572" .
            "18032001280b32212e676f6f676c652e636c6f75642e6461746170726f63" .
            "2e76312e436c75737465724203e0410212450a1d677261636566756c5f64" .
            "65636f6d6d697373696f6e5f74696d656f757418062001280b32192e676f" .
            "6f676c652e70726f746f6275662e4475726174696f6e4203e0410112340a" .
            "0b7570646174655f6d61736b18042001280b321a2e676f6f676c652e7072" .
            "6f746f6275662e4669656c644d61736b4203e0410212170a0a7265717565" .
            "73745f69641807200128094203e041012293010a1444656c657465436c75" .
            "737465725265717565737412170a0a70726f6a6563745f69641801200128" .
            "094203e0410212130a06726567696f6e1803200128094203e0410212190a" .
            "0c636c75737465725f6e616d651802200128094203e0410212190a0c636c" .
            "75737465725f757569641804200128094203e0410112170a0a7265717565" .
            "73745f69641805200128094203e04101225c0a11476574436c7573746572" .
            "5265717565737412170a0a70726f6a6563745f69641801200128094203e0" .
            "410212130a06726567696f6e1803200128094203e0410212190a0c636c75" .
            "737465725f6e616d651802200128094203e041022289010a134c69737443" .
            "6c7573746572735265717565737412170a0a70726f6a6563745f69641801" .
            "200128094203e0410212130a06726567696f6e1804200128094203e04102" .
            "12130a0666696c7465721805200128094203e0410112160a09706167655f" .
            "73697a651802200128054203e0410112170a0a706167655f746f6b656e18" .
            "03200128094203e04101226e0a144c697374436c75737465727352657370" .
            "6f6e736512380a08636c75737465727318012003280b32212e676f6f676c" .
            "652e636c6f75642e6461746170726f632e76312e436c75737465724203e0" .
            "4103121c0a0f6e6578745f706167655f746f6b656e1802200128094203e0" .
            "410322610a16446961676e6f7365436c7573746572526571756573741217" .
            "0a0a70726f6a6563745f69641801200128094203e0410212130a06726567" .
            "696f6e1803200128094203e0410212190a0c636c75737465725f6e616d65" .
            "1802200128094203e0410222310a16446961676e6f7365436c7573746572" .
            "526573756c747312170a0a6f75747075745f7572691801200128094203e0" .
            "410322f8010a135265736572766174696f6e416666696e69747912590a18" .
            "636f6e73756d655f7265736572766174696f6e5f7479706518012001280e" .
            "32322e676f6f676c652e636c6f75642e6461746170726f632e76312e5265" .
            "736572766174696f6e416666696e6974792e547970654203e0410112100a" .
            "036b65791802200128094203e0410112130a0676616c7565731803200328" .
            "094203e04101225f0a045479706512140a10545950455f554e5350454349" .
            "46494544100012120a0e4e4f5f5245534552564154494f4e100112130a0f" .
            "414e595f5245534552564154494f4e100212180a1453504543494649435f" .
            "5245534552564154494f4e100332e30c0a11436c7573746572436f6e7472" .
            "6f6c6c65721280020a0d437265617465436c7573746572122e2e676f6f67" .
            "6c652e636c6f75642e6461746170726f632e76312e437265617465436c75" .
            "73746572526571756573741a1d2e676f6f676c652e6c6f6e6772756e6e69" .
            "6e672e4f7065726174696f6e229f0182d3e493023e22332f76312f70726f" .
            "6a656374732f7b70726f6a6563745f69647d2f726567696f6e732f7b7265" .
            "67696f6e7d2f636c7573746572733a07636c7573746572da411970726f6a" .
            "6563745f69642c726567696f6e2c636c7573746572ca413c0a07436c7573" .
            "7465721231676f6f676c652e636c6f75642e6461746170726f632e76312e" .
            "436c75737465724f7065726174696f6e4d6574616461746112a8020a0d55" .
            "7064617465436c7573746572122e2e676f6f676c652e636c6f75642e6461" .
            "746170726f632e76312e557064617465436c757374657252657175657374" .
            "1a1d2e676f6f676c652e6c6f6e6772756e6e696e672e4f7065726174696f" .
            "6e22c70182d3e493024d32422f76312f70726f6a656374732f7b70726f6a" .
            "6563745f69647d2f726567696f6e732f7b726567696f6e7d2f636c757374" .
            "6572732f7b636c75737465725f6e616d657d3a07636c7573746572da4132" .
            "70726f6a6563745f69642c726567696f6e2c636c75737465725f6e616d65" .
            "2c636c75737465722c7570646174655f6d61736bca413c0a07436c757374" .
            "65721231676f6f676c652e636c6f75642e6461746170726f632e76312e43" .
            "6c75737465724f7065726174696f6e4d657461646174611299020a0d4465" .
            "6c657465436c7573746572122e2e676f6f676c652e636c6f75642e646174" .
            "6170726f632e76312e44656c657465436c7573746572526571756573741a" .
            "1d2e676f6f676c652e6c6f6e6772756e6e696e672e4f7065726174696f6e" .
            "22b80182d3e49302442a422f76312f70726f6a656374732f7b70726f6a65" .
            "63745f69647d2f726567696f6e732f7b726567696f6e7d2f636c75737465" .
            "72732f7b636c75737465725f6e616d657dda411e70726f6a6563745f6964" .
            "2c726567696f6e2c636c75737465725f6e616d65ca414a0a15676f6f676c" .
            "652e70726f746f6275662e456d7074791231676f6f676c652e636c6f7564" .
            "2e6461746170726f632e76312e436c75737465724f7065726174696f6e4d" .
            "6574616461746112c9010a0a476574436c7573746572122b2e676f6f676c" .
            "652e636c6f75642e6461746170726f632e76312e476574436c7573746572" .
            "526571756573741a212e676f6f676c652e636c6f75642e6461746170726f" .
            "632e76312e436c7573746572226b82d3e493024412422f76312f70726f6a" .
            "656374732f7b70726f6a6563745f69647d2f726567696f6e732f7b726567" .
            "696f6e7d2f636c7573746572732f7b636c75737465725f6e616d657dda41" .
            "1e70726f6a6563745f69642c726567696f6e2c636c75737465725f6e616d" .
            "6512d9010a0c4c697374436c757374657273122d2e676f6f676c652e636c" .
            "6f75642e6461746170726f632e76312e4c697374436c7573746572735265" .
            "71756573741a2e2e676f6f676c652e636c6f75642e6461746170726f632e" .
            "76312e4c697374436c757374657273526573706f6e7365226a82d3e49302" .
            "3512332f76312f70726f6a656374732f7b70726f6a6563745f69647d2f72" .
            "6567696f6e732f7b726567696f6e7d2f636c757374657273da411170726f" .
            "6a6563745f69642c726567696f6eda411870726f6a6563745f69642c7265" .
            "67696f6e2c66696c746572128e020a0f446961676e6f7365436c75737465" .
            "7212302e676f6f676c652e636c6f75642e6461746170726f632e76312e44" .
            "6961676e6f7365436c7573746572526571756573741a1d2e676f6f676c65" .
            "2e6c6f6e6772756e6e696e672e4f7065726174696f6e22a90182d3e49302" .
            "50224b2f76312f70726f6a656374732f7b70726f6a6563745f69647d2f72" .
            "6567696f6e732f7b726567696f6e7d2f636c7573746572732f7b636c7573" .
            "7465725f6e616d657d3a646961676e6f73653a012ada411e70726f6a6563" .
            "745f69642c726567696f6e2c636c75737465725f6e616d65ca412f0a1567" .
            "6f6f676c652e70726f746f6275662e456d7074791216446961676e6f7365" .
            "436c7573746572526573756c74731a4bca41176461746170726f632e676f" .
            "6f676c65617069732e636f6dd2412e68747470733a2f2f7777772e676f6f" .
            "676c65617069732e636f6d2f617574682f636c6f75642d706c6174666f72" .
            "6d42710a1c636f6d2e676f6f676c652e636c6f75642e6461746170726f63" .
            "2e7631420d436c75737465727350726f746f50015a40676f6f676c652e67" .
            "6f6c616e672e6f72672f67656e70726f746f2f676f6f676c65617069732f" .
            "636c6f75642f6461746170726f632f76313b6461746170726f6362067072" .
            "6f746f33"
        ), true);

        static::$is_initialized = true;
    }
}

