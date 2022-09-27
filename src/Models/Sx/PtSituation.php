<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\PtSituation
 *
 * @property string $situation_number Unique situation-ID for PtSituationElement. Format: CODESPACE:SituationNumber:ID
 * @property string $creation_time Timestamp for when the situation was created
 * @property string $participant_ref Codespace of the data source
 * @property string $progress Status of a situation message. 'open' or 'closed'
 * @property string|null $source_type Information type: Possible values: 'directReport'
 * @property string|null $source_name Who or what is the source of the situation
 * @property string|null $validity_start Validity period start time
 * @property string|null $validity_end Validity period end time
 * @property string $severity How severely the situation affects public transport services. Enumeration
 * @property int|null $priority Number value from 1 to 10 indicating the priority (urgency) of the situation message
 * @property string $report_type Type of situation report. 'general' or 'incident'
 * @property int|null $planned Whether the situation in question is due to planned events, or an unexpected incident
 * @property string $summary The textual summary of the situation
 * @property string|null $description Expanded textual description of the situation
 * @property string|null $advice Textual advice on how a passenger should react/respond to the situation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereAdvice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereCreationTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereParticipantRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation wherePlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereReportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereSituationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereValidityEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PtSituation whereValidityStart($value)
 * @mixin \Eloquent
 */
class PtSituation extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $table = 'siri_sx_pt_situation';
    protected $primaryKey = 'situation_number';
    protected $keyType = 'string';
    protected $fillable = [
        'situation_number',
        'creation_time',
        'participant_ref',
        'source_type',
        'source_name',
        'progress',
        'validity_start',
        'validity_end',
        'severity',
        'priority',
        'report_type',
        'planned',
        'summary',
        'description',
        'advice',
    ];

    protected $hidden = ['source_name'];
}
