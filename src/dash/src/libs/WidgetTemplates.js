/**
 * Fichier permettant d'inclure l'ensemble des composants des widgets et de faire
 * le lien entre les widgets et les types
 */
import CmdAction from "@/components/Widgets/CmdAction";
import EqLogicAction from "@/components/Widgets/EqLogicAction";
import InfoBinary from "@/components/Widgets/InfoBinary";
import InfoBinaryImg from "@/components/Widgets/InfoBinaryImg";
import InfoNumeric from "@/components/Widgets/InfoNumeric";
import InfoNumericImg from "@/components/Widgets/InfoNumericImg";
import ScenarioAction from "@/components/Widgets/ScenarioAction";
import ScenarioActionImg from "@/components/Widgets/ScenarioActionImg";
import Camera from "@/components/Widgets/Camera";

export default {
    components: {
        Camera,
        CmdAction,
        EqLogicAction,
        InfoBinary,
        InfoBinaryImg,
        InfoNumeric,
        InfoNumericImg,
        ScenarioAction,
        ScenarioActionImg
    }
}